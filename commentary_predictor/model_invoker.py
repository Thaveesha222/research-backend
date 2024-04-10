import os
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing.sequence import pad_sequences
import pickle
from flask import Flask, request, jsonify

app = Flask(__name__)

def load_model_and_tokenizer(model_path, tokenizer_path):
    # Load the model
    model = tf.keras.models.load_model(model_path)

    # Load the tokenizer
    with open(tokenizer_path, 'rb') as handle:
        tokenizer = pickle.load(handle)

    return model, tokenizer

@app.route('/predict_run_type', methods=['POST'])
def predict_run_type():
    commentary = request.json.get('commentary')

    model_path = "./run_type_prediction_model.h5"
    tokenizer_path = "./run_type_tokenizer.pickle"

    model, tokenizer = load_model_and_tokenizer(model_path, tokenizer_path)

    # Tokenize the commentary
    commentary_seq = tokenizer.texts_to_sequences([commentary])
    commentary_seq = pad_sequences(commentary_seq, maxlen=15, padding='post')

    # Predict runs
    predicted_runs = model.predict(commentary_seq)

    # Round the predicted runs to the nearest integer
    rounded_predicted_runs = np.round(predicted_runs).astype(int)

    rounded_predicted_runs_list = rounded_predicted_runs.tolist()

    return jsonify({'predicted_runs': rounded_predicted_runs_list})

@app.route('/predict_run_count', methods=['POST'])
def predict_run_count():
    commentary = request.json.get('commentary')

    model_path = "./run_count_prediction_model.h5"
    tokenizer_path = "./run_count_tokenizer.pickle"

    model, tokenizer = load_model_and_tokenizer(model_path, tokenizer_path)

    # Tokenize the commentary
    commentary_seq = tokenizer.texts_to_sequences([commentary])
    commentary_seq = pad_sequences(commentary_seq, maxlen=15, padding='post')

    # Predict runs
    predicted_runs = model.predict(commentary_seq)

    # Round the predicted runs to the nearest integer
    rounded_predicted_runs = np.round(predicted_runs).astype(int)

    rounded_predicted_runs_list = rounded_predicted_runs.tolist()

    return jsonify({'predicted_runs': rounded_predicted_runs_list})


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0',port=5001)
