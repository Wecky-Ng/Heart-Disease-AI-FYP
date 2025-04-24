# Heart Disease Prediction API

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# This would be replaced with a properly trained model in production
class HeartDiseasePredictor:
    def __init__(self):
        # Initialize a simple model for demonstration
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.scaler = StandardScaler()
        
        # In a real scenario, you would load a pre-trained model
        # self.model = joblib.load('heart_disease_model.pkl')
        # self.scaler = joblib.load('scaler.pkl')
        
    def preprocess_data(self, data):
        """Preprocess input data before prediction"""
        # Convert to DataFrame for easier handling
        df = pd.DataFrame([data])
        
        # Feature engineering would go here
        
        # Scale numerical features
        numerical_features = ['age', 'bmi', 'blood_pressure', 'cholesterol', 'heart_rate', 'blood_sugar']
        df[numerical_features] = self.scaler.fit_transform(df[numerical_features])
        
        return df
    
    def predict(self, data):
        """Make prediction and return risk level"""
        # In a real scenario, this would use the actual trained model
        # For demo, we'll return a random prediction with confidence
        
        # Preprocess the data
        processed_data = self.preprocess_data(data)
        
        # Generate a random probability for demo purposes
        # In production, this would be: prob = self.model.predict_proba(processed_data)[0][1]
        prob = np.random.random()
        
        # Determine risk level based on probability
        if prob < 0.3:
            risk_level = "low"
        elif prob < 0.7:
            risk_level = "medium"
        else:
            risk_level = "high"
            
        return {
            "risk_level": risk_level,
            "probability": round(prob * 100, 2),
            "factors": self.get_contributing_factors(data)
        }
    
    def get_contributing_factors(self, data):
        """Identify top contributing factors to the prediction"""
        # In a real scenario, this would use feature importance from the model
        # For demo, we'll return some plausible factors based on the input
        
        factors = []
        
        if data.get('age', 0) > 60:
            factors.append("Age above 60")
        if data.get('cholesterol', 0) > 240:
            factors.append("High cholesterol")
        if data.get('blood_pressure', 0) > 140:
            factors.append("High blood pressure")
        if data.get('smoking', '') == 'Yes':
            factors.append("Smoking habit")
        if data.get('diabetes', '') == 'Yes':
            factors.append("Diabetes")
        if data.get('family_history', '') == 'Yes':
            factors.append("Family history of heart disease")
        
        # If no specific factors, return general message
        if not factors:
            return ["Multiple factors combined"]
            
        return factors[:3]  # Return top 3 factors

# Initialize predictor
predictor = HeartDiseasePredictor()

@app.route('/predict', methods=['POST'])
def predict():
    """API endpoint for heart disease prediction"""
    try:
        # Get data from request
        data = request.json
        
        # Make prediction
        result = predictor.predict(data)
        
        return jsonify(result)
    
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(debug=True, port=5000)