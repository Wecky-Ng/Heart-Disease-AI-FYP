import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, RandomizedSearchCV
from sklearn.preprocessing import StandardScaler, OneHotEncoder, LabelEncoder
from sklearn.impute import SimpleImputer
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.metrics import classification_report, confusion_matrix, roc_auc_score, precision_recall_curve, auc, make_scorer, f1_score, recall_score, precision_score, RocCurveDisplay, PrecisionRecallDisplay
from imblearn.combine import SMOTETomek # Using SMOTETomek for imbalance handling
from imblearn.pipeline import Pipeline as ImbPipeline
import os
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.decomposition import PCA
import joblib # To save/load model components if needed later
from catboost import CatBoostClassifier, Pool # Using CatBoost

# --- Configuration ---
DATA_FILE = 'heart_2020_cleaned.csv'
# Define the path to your dataset - Update this if your file is elsewhere
DATA_FILE_PATH = DATA_FILE # Assuming the file is in the same directory as the script

# Define the target column name
TARGET_COLUMN = 'HeartDisease' # Based on heart_2020_cleaned.csv

TEST_SIZE = 0.2 # 20% for testing
RANDOM_STATE = 42 # For reproducibility
# Note: RandomizedSearchCV is not used in this script, as we are using the user-provided best params
# N_ITER_SEARCH = 50
# CV_FOLDS = 5

# Define the optimal threshold found by the user's CatBoost tuning
OPTIMAL_THRESHOLD = 0.70 # Based on user's result for max F1

# Directory to save generated graphs - Make sure this directory exists
GRAPH_SAVE_DIR = 'model_graphs'
os.makedirs(GRAPH_SAVE_DIR, exist_ok=True) # Create the directory if it doesn't exist
print(f"Graphs will be saved to: {GRAPH_SAVE_DIR}")

# Define the best hyperparameters provided by the user for CatBoost
BEST_CATBOOST_PARAMS = {
    'learning_rate': 0.01,
    'l2_leaf_reg': 7,
    'iterations': 1500, # Using the iteration count from bestIteration
    'depth': 6,
    'border_count': 64,
    'bagging_temperature': 0.2,
    'random_state': RANDOM_STATE,
    'verbose': 0, # Reduce verbosity for cleaner output during script run
    'early_stopping_rounds': 50 # Add early stopping for robustness
}


# --- Load Data ---
print(f"Attempting to load data from: {DATA_FILE_PATH}")
try:
    df = pd.read_csv(DATA_FILE_PATH)
    print("Data loaded successfully.")
    print("Data shape:", df.shape)
    print("Class distribution:\n", df[TARGET_COLUMN].value_counts(normalize=True))
except FileNotFoundError:
    print(f"Error: Data file '{DATA_FILE}' not found at '{DATA_FILE_PATH}'.")
    print("Please ensure the CSV file is in the correct directory or update the DATA_FILE_PATH.")
    exit() # Exit if data is not found

# --- Data Analysis and Feature Identification ---
print("\nPerforming Data Analysis and Feature Identification...")

# Identify features based on the provided column list, excluding the target
feature_columns = [col for col in df.columns if col != TARGET_COLUMN]

# Separate features (X) and target (y)
X = df[feature_columns]
y = df[TARGET_COLUMN]

# Encode the target variable ('No' -> 0, 'Yes' -> 1)
le = LabelEncoder()
y = le.fit_transform(y) # Now y contains 0s and 1s
print(f"\nTarget variable encoded. Mapping: {dict(zip(le.classes_, le.transform(le.classes_)))}")

# Identify numerical and categorical features based on data types
# Explicitly handle known categorical features even if they appear numerical (like Sex, Race, etc.)
numerical_features = X.select_dtypes(include=np.number).columns.tolist()
categorical_features = X.select_dtypes(exclude=np.number).columns.tolist()

# Manually adjust based on the dataset's known structure and your requirements
categorical_features_manual = [
    'Smoking', 'AlcoholDrinking', 'Stroke', 'DiffWalking', 'Sex',
    'AgeCategory', 'Race', 'Diabetic', 'PhysicalActivity', 'GenHealth',
    'Asthma', 'KidneyDisease', 'SkinCancer'
]
numerical_features = [f for f in numerical_features if f not in categorical_features_manual]
categorical_features = list(set(categorical_features + [f for f in categorical_features_manual if f in X.columns]))

# Ensure all features are accounted for
all_identified_features = numerical_features + categorical_features
if set(all_identified_features) != set(feature_columns):
    print("Warning: Feature identification mismatch!")
    print("Features in data but not identified:", set(feature_columns) - set(all_identified_features))
    print("Identified features not in data:", set(all_identified_features) - set(feature_columns))


print(f"\nIdentified Numerical features ({len(numerical_features)}): {numerical_features}")
print(f"Identified Categorical features ({len(categorical_features)}): {categorical_features}")

# --- Preprocessing ---

# Create preprocessing pipelines for numerical and categorical features
# Numerical: Impute missing values with median, then scale
numerical_transformer = Pipeline(steps=[
    ('imputer', SimpleImputer(strategy='median')),
    ('scaler', StandardScaler())
])

# Categorical: Impute missing values with most frequent, then one-hot encode
# CatBoost can handle categorical features directly, but preprocessing might still be needed
# depending on how you want to handle missing values and the specific CatBoost parameters.
# For consistency with previous pipelines and handling potential future models,
# we'll keep the preprocessing pipeline, but note that CatBoost has built-in handling.
categorical_transformer = Pipeline(steps=[
    ('imputer', SimpleImputer(strategy='most_frequent')),
    ('onehot', OneHotEncoder(handle_unknown='ignore', drop='first')) # drop='first' to avoid multicollinearity
])

# Combine preprocessing steps using ColumnTransformer
preprocessor = ColumnTransformer(
    transformers=[
        ('num', numerical_transformer, numerical_features),
        ('cat', categorical_transformer, categorical_features)
    ],
    remainder='passthrough' # Keep any columns not specified (shouldn't be any)
)

# --- Split Data ---
X_train, X_test, y_train, y_test = train_test_split(
    X, y,
    test_size=TEST_SIZE,
    random_state=RANDOM_STATE,
    stratify=y # Important for imbalanced datasets to keep proportion in splits
)
print(f"\nData split into training ({X_train.shape[0]} samples) and testing ({X_test.shape[0]} samples).")
print("Training set class distribution:\n", pd.Series(y_train).value_counts(normalize=True))


# --- Model Training (CatBoost with User-Provided Best Params) ---
print("\nTraining CatBoost model with user-provided best hyperparameters...")

# Create a pipeline that first preprocesses, then trains the CatBoost model
# Note: SMOTETomek is typically used with models that are sensitive to imbalance
# CatBoost has built-in mechanisms like scale_pos_weight and handling categorical features.
# If your user's tuning included SMOTETomek in the pipeline, keep ImbPipeline.
# If the tuning was done directly on CatBoost params (including scale_pos_weight),
# a standard scikit-learn Pipeline might suffice depending on how imbalance was handled.
# Assuming the user's best params were for CatBoost applied AFTER preprocessing.
# If SMOTETomek was part of their best pipeline, you would use ImbPipeline here.
# For this script, we'll use a standard Pipeline after preprocessing, assuming
# imbalance was handled by CatBoost's parameters or other methods in the user's tuning process.

# Create a standard pipeline (preprocessing + classifier)
pipeline = Pipeline(steps=[
    ('preprocessor', preprocessor),
    ('classifier', CatBoostClassifier(**BEST_CATBOOST_PARAMS)) # Use CatBoost with best params
])


# Train the model
pipeline.fit(X_train, y_train)

print("\nCatBoost Model Training Complete.")
best_model = pipeline # The pipeline is our best model here


# --- Evaluation of the Best Model ---
print("\nEvaluating the CatBoost model on the test set...")

# Get predictions and probabilities
y_pred_default = best_model.predict(X_test)
y_pred_proba = best_model.predict_proba(X_test)[:, 1] # Probabilities for the positive class (1)

# --- Metrics (Default Threshold 0.5) ---
print("\nClassification Report (CatBoost Model - Default Threshold 0.5):")
print(classification_report(y_test, y_pred_default, target_names=le.classes_, zero_division=0))

print("\nConfusion Matrix (CatBoost Model - Default Threshold 0.5):")
cm_default = confusion_matrix(y_test, y_pred_default)
print(cm_default)

roc_auc = roc_auc_score(y_test, y_pred_proba)
print(f"\nArea Under the ROC Curve (AUC-ROC): {roc_auc:.4f}")

precision, recall, _ = precision_recall_curve(y_test, y_pred_proba)
pr_auc = auc(recall, precision)
print(f"Area Under the Precision-Recall Curve (AUC-PR): {pr_auc:.4f}")


# --- Applying Optimal Threshold ---
print(f"\nApplying Optimal Threshold = {OPTIMAL_THRESHOLD:.4f} (Based on user's tuning for max F1)...")

# Apply the optimal threshold to make predictions
y_pred_optimal = (y_pred_proba >= OPTIMAL_THRESHOLD).astype(int)

# --- Evaluation of the Best Model (Optimal Threshold) ---
print(f"\nClassification Report (CatBoost Model - Optimal Threshold = {OPTIMAL_THRESHOLD:.4f}):")
print(classification_report(y_test, y_pred_optimal, target_names=le.classes_, zero_division=0))

print(f"\nConfusion Matrix (CatBoost Model - Optimal Threshold = {OPTIMAL_THRESHOLD:.4f}):")
cm_optimal = confusion_matrix(y_test, y_pred_optimal)
print(cm_optimal)


# --- Generate and Save Graphs for model_detail.php ---
print(f"\nGenerating and saving graphs to '{GRAPH_SAVE_DIR}'...")

# 1. Correlation Matrix (Numerical Features)
if numerical_features:
    plt.figure(figsize=(10, 8))
    # Ensure the target is included for correlation calculation
    corr_df = df[numerical_features + [TARGET_COLUMN]].copy()
    # Convert target to numerical for correlation calculation
    corr_df[TARGET_COLUMN] = le.transform(corr_df[TARGET_COLUMN])
    correlation_matrix = corr_df.corr()
    sns.heatmap(correlation_matrix, annot=True, cmap='coolwarm', fmt=".2f", linewidths=".5", annot_kws={"size": 8}) # Reduced annot font size
    plt.title('Correlation Matrix of Numerical Features and Target')
    plt.tight_layout()
    plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'correlation_matrix.png'))
    plt.close()
    print("- Saved correlation_matrix.png")
else:
    print("- No numerical features to plot correlation matrix.")


# 2. PCA Plot (on Preprocessed Training Data)
# Apply preprocessing to the training data to get the transformed features
# Use fit_transform on X_train
X_train_preprocessed = preprocessor.fit_transform(X_train)

# Perform PCA to reduce to 2 components for visualization
pca = PCA(n_components=2, random_state=RANDOM_STATE)
X_train_pca = pca.fit_transform(X_train_preprocessed)

print(f"Explained variance ratio by first 2 PCA components: {pca.explained_variance_ratio_.sum():.4f}")

plt.figure(figsize=(10, 8))
scatter = plt.scatter(X_train_pca[:, 0], X_train_pca[:, 1], c=y_train, cmap='viridis', alpha=0.6)
plt.title('PCA of Preprocessed Training Data (2 Components)')
plt.xlabel('Principal Component 1')
plt.ylabel('Principal Component 2')
# Add a legend using the original class names
legend_labels = le.classes_
legend_elements = scatter.legend_elements()[0]
plt.legend(legend_elements, legend_labels, title="Heart Disease Status")
plt.grid(True)
plt.tight_layout()
plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'pca_plot.png'))
plt.close()
print("- Saved pca_plot.png")


# 3. Feature Importance Plot (from CatBoost)
try:
    # Get feature importances from the trained CatBoost classifier
    catboost_classifier = best_model.named_steps['classifier']
    # CatBoost feature importance uses the original feature names if provided
    # If not provided, it uses column indices. We need the names after preprocessing.

    # Get feature names after preprocessing (including one-hot encoding)
    feature_names_processed = best_model.named_steps['preprocessor'].get_feature_names_out()

    # Get feature importances
    feature_importances = catboost_classifier.get_feature_importance()

    # Create a pandas Series for easier viewing and plotting
    # Ensure the indices match the feature_importances array
    importance_series = pd.Series(feature_importances, index=feature_names_processed)

    # Sort feature importances and select top N for plotting
    top_n = 20 # Display top 20 features
    sorted_importance_series = importance_series.sort_values(ascending=False).head(top_n)

    plt.figure(figsize=(12, 8))
    sns.barplot(x=sorted_importance_series.values, y=sorted_importance_series.index, palette='viridis')
    plt.title(f'Top {top_n} Feature Importances (CatBoost)')
    plt.xlabel('Importance Score')
    plt.ylabel('Feature')
    plt.tight_layout()
    plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'feature_importance.png'))
    plt.close()
    print(f"- Saved feature_importance.png (Top {top_n})")

except Exception as e:
    print(f"- Could not generate feature importance plot: {e}")
    print(f"Error details: {e}") # Print error details for debugging


# 4. ROC Curve
plt.figure(figsize=(8, 6))
RocCurveDisplay.from_estimator(best_model, X_test, y_test, name='CatBoost')
plt.title('ROC Curve')
plt.grid(True)
plt.tight_layout()
plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'roc_curve.png'))
plt.close()
print("- Saved roc_curve.png")


# 5. Precision-Recall Curve
plt.figure(figsize=(8, 6))
PrecisionRecallDisplay.from_estimator(best_model, X_test, y_test, name='CatBoost')
plt.title('Precision-Recall Curve')
plt.grid(True)
plt.tight_layout()
plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'precision_recall_curve.png'))
plt.close()
print("- Saved precision_recall_curve.png")


# 6. Confusion Matrix Heatmap (Optimal Threshold)
plt.figure(figsize=(8, 6))
sns.heatmap(cm_optimal, annot=True, fmt='d', cmap='Blues', xticklabels=le.classes_, yticklabels=le.classes_)
plt.title(f'Confusion Matrix (Optimal Threshold = {OPTIMAL_THRESHOLD:.4f})')
plt.xlabel('Predicted Label')
plt.ylabel('True Label')
plt.tight_layout()
plt.savefig(os.path.join(GRAPH_SAVE_DIR, 'confusion_matrix_optimal.png'))
plt.close()
print("- Saved confusion_matrix_optimal.png")

print("\nGraph generation complete.")

# --- Save Model and Preprocessor (Optional - for deployment) ---
# You can uncomment this section if you want to save the trained model and preprocessor
# for later deployment.
# model_filename = 'heart_disease_catboost_model_2020.joblib'
# preprocessor_filename = 'heart_disease_preprocessor_2020.joblib'
# label_encoder_filename = 'heart_disease_label_encoder_2020.joblib'
# optimal_threshold_filename = 'heart_disease_optimal_threshold_2020.joblib'
# processed_feature_names_filename = 'processed_feature_names_2020.joblib' # Recommended to save

# try:
#     joblib.dump(best_model, model_filename)
#     print(f"\nBest model saved to {model_filename}")
#
#     joblib.dump(preprocessor, preprocessor_filename)
#     print(f"Preprocessor saved to {preprocessor_filename}")
#
#     joblib.dump(le, label_encoder_filename)
#     print(f"Label encoder saved to {label_encoder_filename}")
#
#     joblib.dump(OPTIMAL_THRESHOLD, optimal_threshold_filename) # Save the optimal threshold constant
#     print(f"Optimal threshold saved to {optimal_threshold_filename}")
#
#     # Save the list of feature names after preprocessing
#     processed_feature_names = best_model.named_steps['preprocessor'].get_feature_names_out()
#     joblib.dump(processed_feature_names, processed_feature_names_filename)
#     print(f"Processed feature names saved to {processed_feature_names_filename}")
#
# except Exception as e:
#     print(f"\nError saving model components: {e}")

print("\n--- Analysis Complete ---")
print("Review the printed metrics and check the generated image files in the 'model_graphs' directory.")
