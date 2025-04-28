# =============================================
# Ensemble Heart Disease Predictor: CatBoost + LightGBM + Stacking
# =============================================

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, StratifiedKFold, RandomizedSearchCV
from sklearn.metrics import roc_auc_score, classification_report, precision_recall_curve, make_scorer, f1_score
from sklearn.preprocessing import LabelEncoder
from sklearn.pipeline import Pipeline
from sklearn.compose import ColumnTransformer
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.impute import SimpleImputer
from catboost import CatBoostClassifier
from lightgbm import LGBMClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import StackingClassifier
import joblib
from scipy.stats import uniform, randint

# --- Configuration ---
DATA_FILE = 'heart_2020_cleaned.csv'
TARGET = 'HeartDisease'
TEST_SIZE = 0.2
RANDOM_STATE = 42
N_ITER_SEARCH = 50 # Increased iterations for more thorough hyperparameter search.
CV_FOLDS = 3 # Number of cross-validation folds.
SCORING_METRIC = 'f1_weighted' # Metric to optimize during search ('roc_auc', 'f1_weighted', 'accuracy')
SAVE_MODEL_PATH = 'tuned_ensemble_cat_lgbm_v4.pkl' # Updated save path for new tuning run

# --- Load & Prepare Data ---
print(f"Loading data from: {DATA_FILE}")
try:
    df = pd.read_csv(DATA_FILE)
    print(f"Data loaded: {df.shape[0]} rows, {df.shape[1]} cols")
except FileNotFoundError:
    raise FileNotFoundError(f"Could not find {DATA_FILE}. Ensure it is in the working directory.")

# --- Encode Target ---
if df[TARGET].dtype != 'int':
    le = LabelEncoder()
    df[TARGET] = le.fit_transform(df[TARGET])  # No->0, Yes->1
    print(f"Target variable encoded. Mapping: {dict(zip(le.classes_, le.transform(le.classes_)))}")

FEATURES = [c for c in df.columns if c != TARGET]
cat_cols = df[FEATURES].select_dtypes(include='object').columns.tolist()
num_cols = df[FEATURES].select_dtypes(exclude='object').columns.tolist()
print(f"Numerical: {num_cols}\nCategorical: {cat_cols}")

# --- Train/Test Split ---
train_df, test_df = train_test_split(
    df, test_size=TEST_SIZE, stratify=df[TARGET], random_state=RANDOM_STATE
)
X_train, y_train = train_df[FEATURES], train_df[TARGET]
X_test, y_test   = test_df[FEATURES], test_df[TARGET]
print(f"Data split: Train {X_train.shape}, Test {X_test.shape}")

# --- Preprocessing Pipelines ---
num_pipe = Pipeline([
    ('impute', SimpleImputer(strategy='median')),
    ('scale',   StandardScaler())
])
cat_pipe = Pipeline([
    ('impute', SimpleImputer(strategy='most_frequent')),
    ('ohe',    OneHotEncoder(handle_unknown='ignore', drop='first', sparse_output=False))
])
preprocessor = ColumnTransformer([
    ('num', num_pipe, num_cols),
    ('cat', cat_pipe, cat_cols)
], remainder='passthrough') # Keep other columns if any

# --- Define Base Models and Parameter Search Spaces ---

# CatBoost
cb_pipeline = Pipeline([('prep', preprocessor), ('clf', CatBoostClassifier(random_seed=RANDOM_STATE, verbose=0, loss_function='Logloss', eval_metric='AUC'))])
cb_param_dist = {
    'clf__iterations': randint(800, 3000), # Further expanded range
    'clf__learning_rate': uniform(0.003, 0.15), # Slightly wider range (loc=0.003, scale=0.147)
    'clf__depth': randint(4, 13), # Wider range
    'clf__l2_leaf_reg': uniform(0.1, 19.9), # Wider range (loc=0.1, scale=19.8)
    'clf__border_count': randint(32, 255),
    'clf__bagging_temperature': uniform(0, 1.0),
    'clf__class_weights': [[1, w] for w in np.linspace(2, 30, 10)] # Wider range and more options
}

# LightGBM
lgb_pipeline = Pipeline([('prep', preprocessor), ('clf', LGBMClassifier(random_state=RANDOM_STATE, class_weight='balanced'))])
lgb_param_dist = {
    'clf__n_estimators': randint(800, 3000), # Further expanded range
    'clf__learning_rate': uniform(0.003, 0.15), # Slightly wider range (loc=0.003, scale=0.147)
    'clf__num_leaves': randint(25, 180), # Wider range
    'clf__colsample_bytree': uniform(0.4, 0.6), # Wider range [0.4, 1.0)
    'clf__subsample': uniform(0.4, 0.6),       # Wider range [0.4, 1.0)
    'clf__reg_alpha': uniform(0, 3), # Wider range
    'clf__reg_lambda': uniform(0, 3) # Wider range
}

# --- Hyperparameter Tuning with RandomizedSearchCV ---
cv_strategy = StratifiedKFold(n_splits=CV_FOLDS, shuffle=True, random_state=RANDOM_STATE)

def tune_model(pipeline, param_dist, name):
    print(f"\nTuning {name}...")
    random_search = RandomizedSearchCV(
        pipeline,
        param_distributions=param_dist,
        n_iter=N_ITER_SEARCH,
        cv=cv_strategy,
        scoring=SCORING_METRIC,
        random_state=RANDOM_STATE,
        n_jobs=-1, # Use all available cores
        verbose=1
    )
    random_search.fit(X_train, y_train)
    print(f"Best {SCORING_METRIC} for {name}: {random_search.best_score_:.4f}")
    print(f"Best parameters for {name}: {random_search.best_params_}")
    return random_search.best_estimator_

best_cb_pipeline = tune_model(cb_pipeline, cb_param_dist, 'CatBoost')
best_lgb_pipeline = tune_model(lgb_pipeline, lgb_param_dist, 'LightGBM')

# --- Stacking Ensemble Construction with Tuned Models ---
estimators = [
    ('catboost', best_cb_pipeline),
    ('lgbm',     best_lgb_pipeline)
]

# Define meta-learner (Logistic Regression) and its search space
# Tuning the meta-learner as well
meta_learner = LogisticRegression(class_weight='balanced', max_iter=2000, random_state=RANDOM_STATE, solver='liblinear')
meta_param_dist = {
    'final_estimator__C': uniform(0.01, 10) # Search space for regularization strength
}

stack = StackingClassifier(
    estimators=estimators,
    final_estimator=meta_learner,
    cv=cv_strategy, # Use same CV strategy for meta-learner training
    passthrough=False, # Features for final estimator are predictions of base estimators
    n_jobs=-1
)

# --- Tune the Stacking Classifier (including meta-learner) ---
print("\nTuning the Stacking ensemble (including meta-learner)...")
# Note: Tuning the whole stack can be computationally expensive.
# We are tuning only the final_estimator here for simplicity after tuning base models.
# For full stack tuning, wrap StackingClassifier in RandomizedSearchCV.
# Here, we'll tune the final_estimator separately *after* base models are tuned.
# A more integrated approach might tune base and meta learners together if resources allow.

# Let's refine the approach: Use the tuned base models and then tune the StackingClassifier's final_estimator
stack_tuned_bases = StackingClassifier(
    estimators=estimators, # Using best_cb_pipeline and best_lgb_pipeline
    final_estimator=meta_learner, # Placeholder, will be tuned
    cv=cv_strategy,
    passthrough=False,
    n_jobs=-1
)

# Tune the final_estimator of the stack
stack_search = RandomizedSearchCV(
    stack_tuned_bases, # Pass the stack with tuned base estimators
    param_distributions=meta_param_dist, # Search space for final_estimator__C
    n_iter=20, # Increased iterations for tuning just the meta-learner
    cv=cv_strategy,
    scoring=SCORING_METRIC,
    random_state=RANDOM_STATE,
    n_jobs=-1,
    verbose=1
)

print("\nTraining/Tuning final Stacking ensemble model (meta-learner)...")
stack_search.fit(X_train, y_train)

print(f"Best {SCORING_METRIC} for Stacking meta-learner: {stack_search.best_score_:.4f}")
print(f"Best parameters for Stacking meta-learner: {stack_search.best_params_}")
best_stack = stack_search.best_estimator_

# --- Evaluate on Test Set ---
print("\nEvaluating final tuned ensemble on test set...")
proba = best_stack.predict_proba(X_test)[:,1]
auc_score = roc_auc_score(y_test, proba)
print(f"Hold-out AUC: {auc_score:.4f}")

# Threshold tuning for best F1
prec, rec, thr = precision_recall_curve(y_test, proba)
# Add small epsilon to avoid division by zero
f1s = 2 * prec * rec / (prec + rec + 1e-12)
# Handle potential NaN values in f1s if prec or rec are zero
f1s = np.nan_to_num(f1s)
best_idx = np.argmax(f1s)
best_thr = thr[best_idx]

print(f"Best threshold for F1 = {best_thr:.4f} (F1 = {f1s[best_idx]:.4f})")

# Final classification report using the best F1 threshold
pred = (proba >= best_thr).astype(int)
accuracy = (pred == y_test).mean()
print(f"Accuracy @ optimal F1 threshold: {accuracy:.4f}")
print("\nClassification Report @ optimal F1 threshold:")
print(classification_report(y_test, pred, target_names=['No','Yes']))

# --- Save Tuned Ensemble Model ---
joblib.dump(best_stack, SAVE_MODEL_PATH)
print(f"\nSaved tuned ensemble model to '{SAVE_MODEL_PATH}'")

print("\nScript finished.")
