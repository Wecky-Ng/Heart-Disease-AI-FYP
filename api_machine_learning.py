import os
import joblib
import pandas as pd
import numpy as np
from flask import Flask, request, jsonify
# Removed database imports: mysql.connector, Error
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

app = Flask(__name__)

# --- Removed Database Configuration ---

# --- Model Loading ---
MODEL_PATH = 'tuned_ensemble_cat_lgbm_v4.pkl' # Ensure this model file is in the same directory or provide the correct path
model = None
try:
    model = joblib.load(MODEL_PATH)
    print(f"Model '{MODEL_PATH}' loaded successfully.")
except FileNotFoundError:
    print(f"Error: Model file not found at '{MODEL_PATH}'. Ensure the file exists.")
    # Depending on requirements, you might want to exit or handle this differently
    exit(1) # Exit if model can't be loaded
except Exception as e:
    print(f"Error loading model: {e}")
    exit(1)

# --- Removed Database Connection Function ---

# --- Removed Database Interaction Functions (save_prediction_history, update_last_test_record) ---

# --- API Endpoint ---
@app.route('/predict', methods=['POST'])
def predict():
    if model is None:
        return jsonify({'error': 'Model not loaded'}), 500

    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'No input data provided'}), 400

        # --- Data Preprocessing ---
        # Create a DataFrame with the expected feature names
        # The order MUST match the order the model was trained on.
        # Adjust feature names based on the actual model requirements and incoming JSON keys.
        # Example feature names based on user_prediction_history table (adjust as needed):
        feature_names = [
            'BMI', 'Smoking', 'AlcoholDrinking', 'Stroke', 'PhysicalHealth',
            'MentalHealth', 'DiffWalking', 'Sex', 'AgeCategory', 'Race', 'Diabetic',
            'PhysicalActivity', 'GenHealth', 'SleepTime', 'Asthma', 'KidneyDisease',
            'SkinCancer'
        ]
        # Create a mapping for Age to AgeCategory if needed by the model
        # Example: Map actual age to categories like '18-24', '25-29', etc.
        # This depends heavily on how the model was trained.
        # For now, assuming the model takes numerical age directly or it's handled
        # If AgeCategory is needed, you'll need to implement the mapping logic here.
        # Example placeholder if AgeCategory is needed:
        # age_map = { ... } # Define your age to category mapping
        # data['AgeCategory'] = age_map.get(int(data.get('Age', 0)), default_category)

        # Ensure all expected features are present, provide defaults if necessary
        # Handle potential missing 'AgeCategory' if it's derived from 'Age'
        if 'Age' in data and 'AgeCategory' not in data:
             # Placeholder: Add logic here to derive AgeCategory from Age if needed by the model
             # For now, we assume AgeCategory is either provided or not strictly required
             # If it IS required and not provided, the model might fail. Adjust as needed.
             pass # Or assign a default/derived value

        input_data = {feature: data.get(feature, 0) for feature in feature_names}

        # Convert to DataFrame
        df = pd.DataFrame([input_data])

        # Ensure correct data types (example, adjust as needed)
        # df = df.astype({'Age': int, 'BMI': float, ...})

        # --- Prediction ---
        prediction_proba = model.predict_proba(df)[0] # Get probabilities for class 0 and 1
        prediction = np.argmax(prediction_proba) # Get the class with the highest probability
        confidence = prediction_proba[prediction] # Get the confidence score for the predicted class

        # --- Removed Conditional Database Saving Logic ---

        # Return prediction result and confidence
        return jsonify({
            'prediction': int(prediction), # Ensure prediction is JSON serializable (int)
            'confidence': float(confidence) # Ensure confidence is JSON serializable (float)
        })

    except KeyError as e:
        # Handle cases where expected keys are missing in the input JSON
        return jsonify({'error': f'Missing input feature: {e}'}), 400
    except ValueError as e:
        # Handle cases where data cannot be converted to the expected type (e.g., float, int)
        return jsonify({'error': f'Invalid data format: {e}'}), 400
    except Exception as e:
        print(f"Error during prediction: {e}") # Log the error server-side
        return jsonify({'error': 'An error occurred during prediction.'}), 500

if __name__ == '__main__':
    # Use environment variable for port or default to 5000
    port = int(os.environ.get('PORT', 5000))
    # Run on 0.0.0.0 to be accessible externally if needed
    app.run(host='0.0.0.0', port=port, debug=True) # Set debug=False for production