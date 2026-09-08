<?php

return [
    'base_url' => env('ML_SERVICE_BASE_URL', 'http://127.0.0.1:8001'),
    'timeout' => (int) env('ML_SERVICE_TIMEOUT', 10),
    'predict_rop_endpoint' => env('ML_SERVICE_PREDICT_ROP_ENDPOINT', '/api/v1/predict-rop'),
    'model_metrics_endpoint' => env('ML_SERVICE_MODEL_METRICS_ENDPOINT', '/api/v1/model-metrics'),
];
