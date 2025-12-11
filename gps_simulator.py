
import requests
import time
import math
import random

# CONFIGURATION
API_URL = "http://127.0.0.1:8000/api/record"
PROJECT_ID = 9 # Updated to ID 1 (Test Project)
DELAY = 10       # Use 1s for quicker updates (change if needed)
TOTAL_POINTS = 50 

# STARTING POINT (Monas, Jakarta)
START_LAT = -6.175392
START_LON = 106.827153
STEP_SIZE = 0.0005 # Seberapa jauh langkahnya (random walk)

def generate_random_walk(lat, lon):
    # Random change in lat/long
    delta_lat = random.uniform(-STEP_SIZE, STEP_SIZE)
    delta_lon = random.uniform(-STEP_SIZE, STEP_SIZE)
    
    new_lat = lat + delta_lat
    new_lon = lon + delta_lon
    
    # Variasi Altitude & Speed
    altitude = 10 + random.uniform(-1, 1)
    speed = random.uniform(0.0, 40.0) # Speed acak
    pressure = 1013 + random.uniform(-2, 2)

    return {
        "project_id": PROJECT_ID,
        "latitude": new_lat,
        "longitude": new_lon,
        "altitude": round(altitude, 2),
        "speed": round(speed, 2),
        "pressure": round(pressure, 2)
    }

def send_data(data):
    try:
        response = requests.post(API_URL, json=data)
        if response.status_code == 201:
            print(f"[SUCCESS] Sent: Lat={data['latitude']:.6f}, Lon={data['longitude']:.6f}, Speed={data['speed']} m/s")
        else:
            print(f"[ERROR] Failed: {response.text}")
    except Exception as e:
        print(f"[ERROR] Connection failed: {e}")

if __name__ == "__main__":
    print(f"=== RANDOM WALK GPS SIMULATOR STARTED ===")
    print(f"Target: {API_URL} (Project ID: {PROJECT_ID})")
    print(f"Sending {TOTAL_POINTS} data points every {DELAY} seconds...\n")

    # Initialize Position
    current_lat = START_LAT
    current_lon = START_LON

    for i in range(TOTAL_POINTS):
        data = generate_random_walk(current_lat, current_lon)
        
        # Update current position for next step
        current_lat = data['latitude']
        current_lon = data['longitude']

        send_data(data)
        time.sleep(DELAY)

    print("\n=== SIMULATION FINISHED ===")
