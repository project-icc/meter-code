#include <PZEM004Tv30.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFiManager.h>
#include <SoftwareSerial.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <ArduinoJson.h>

// Configuration
const char* serverUrl = "https://www.meter.it-icc.com/box/nanoConnect.php";
const char* relayControlUrl = "https://www.meter.it-icc.com/box/relayControl.php";
const String deviceId = "002";

// EEPROM Addresses
const int EEPROM_ENERGY_ADDR = 0;
const int EEPROM_RELAY_STATE_ADDR = 4;
const int EEPROM_CONFIG_ADDR = 8;

// Pin Definitions
const uint8_t RX_PIN = D5;
const uint8_t TX_PIN = D6;
const uint8_t RELAY_PIN = D0;
const uint8_t SWITCH1_PIN = D3;
const uint8_t SWITCH2_PIN = D7;
const uint8_t LED_PIN = LED_BUILTIN;

// OLED Configuration
const uint8_t SCREEN_WIDTH = 128;
const uint8_t SCREEN_HEIGHT = 64;
const int8_t OLED_RESET = -1;

// Timing Constants
const unsigned long SEND_INTERVAL = 8000;
const unsigned long DISPLAY_INTERVAL = 3000;
const unsigned long RELAY_CHECK_INTERVAL = 5000;
const unsigned long SERVER_UPDATE_MIN_INTERVAL = 2000;
const unsigned long DEBOUNCE_DELAY = 200;
const unsigned long WATCHDOG_INTERVAL = 60000;
const unsigned long EEPROM_SAVE_INTERVAL = 300000;

// Thresholds and Limits
const float MAX_CURRENT = 100.0;
const float MAX_POWER = 22000.0;
const float VOLTAGE_MIN = 180.0;
const float VOLTAGE_MAX = 260.0;

// Global Objects
SoftwareSerial pzemSerial(RX_PIN, TX_PIN);
PZEM004Tv30 pzem(pzemSerial);
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
ESP8266WebServer webServer(80);

// Configuration structure
struct DeviceConfig {
    float currentLimit;
    float powerLimit;
    bool autoReconnect;
    uint8_t displayBrightness;
    
    DeviceConfig() : currentLimit(MAX_CURRENT), powerLimit(MAX_POWER), 
                     autoReconnect(true), displayBrightness(255) {}
};

// Global Variables
uint8_t currentPage = 1;
bool relayState = true;
bool overloadDetected = false;
unsigned long lastPageSwitch = 0;
unsigned long lastServerUpdate = 0;
unsigned long lastRelayCheck = 0;
unsigned long lastEepromSave = 0;
unsigned long lastWatchdogReset = 0;
float totalEnergy = 0.0;
DeviceConfig deviceConfig;

// Power Readings Structure
struct PowerReadings {
    float voltage;
    float current;
    float power;
    float energy;
    float frequency;
    float pf;
    bool isValid;
    bool isOverload;
    String errorMessage;

    PowerReadings() : voltage(0), current(0), power(0), energy(0), 
                     frequency(0), pf(0), isValid(false), 
                     isOverload(false), errorMessage("") {}
                     
    bool validateReadings() {
        isValid = (voltage >= VOLTAGE_MIN && voltage <= VOLTAGE_MAX &&
                  current >= 0 && current <= deviceConfig.currentLimit &&
                  frequency >= 45 && frequency <= 65);
                  
        isOverload = (current > deviceConfig.currentLimit || power > deviceConfig.powerLimit);
        
        if (!isValid) {
            errorMessage = F("Invalid readings detected");
        } else if (isOverload) {
            errorMessage = F("Overload detected!");
        } else {
            errorMessage = "";
        }
        
        return isValid && !isOverload;
    }
};

void initializeWiFi() {
    WiFiManager wifiManager;
    
    display.clearDisplay();
    display.setTextSize(1);
    display.setCursor(10, 20);
    display.println(F("Connect to:"));
    display.println(F("AutoConnectAP003"));
    display.println(F("IP: 192.168.4.1"));
    display.display();
    
    wifiManager.autoConnect("AutoConnectAP003");
    
    display.clearDisplay();
    display.setTextSize(2);
    display.setCursor(10, 20);
    display.println(F("Connected!"));
    display.display();
    delay(2000);
}

void sendToServer(const PowerReadings& readings) {
    if (!WiFi.isConnected()) {
        Serial.println("WiFi not connected - skipping data send");
        return;
    }

    HTTPClient http;
    WiFiClientSecure client;
    client.setInsecure();

    String postData = "device_id=" + deviceId +
                     "&voltage=" + String(readings.voltage, 2) +
                     "&current=" + String(readings.current, 2) +
                     "&power=" + String(readings.power, 2) +
                     "&energy=" + String(readings.energy, 2) +
                     "&frequency=" + String(readings.frequency, 2) +
                     "&power_factor=" + String(readings.pf, 2) +
                     "&total_energy=" + String(totalEnergy, 2);

    http.begin(client, serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpResponseCode = http.POST(postData);
    
    if (httpResponseCode > 0) {
        String response = http.getString();
        char buffer[100];
        snprintf(buffer, sizeof(buffer), "HTTP Response: %d - %s\n", httpResponseCode, response.c_str());
        Serial.print(buffer);
    } else {
        char buffer[50];
        snprintf(buffer, sizeof(buffer), "HTTP Error: %d\n", httpResponseCode);
        Serial.print(buffer);
    }

    http.end();
}

PowerReadings readPowerMeasurements() {
    PowerReadings readings;
    
    readings.voltage = pzem.voltage();
    readings.current = pzem.current();
    readings.power = pzem.power();
    readings.energy = pzem.energy();
    readings.frequency = pzem.frequency();
    readings.pf = pzem.pf();
    
    readings.validateReadings();
    return readings;
}

void updateDisplay(const PowerReadings& readings) {
    display.clearDisplay();
    display.setCursor(0, 0);
    display.setTextSize(2);
    
    switch (currentPage) {
        case 1:
            display.println(F("Voltage:"));
            display.printf("%.1fV\n", readings.voltage);
            display.println(F("Current:"));
            display.printf("%.2fA", readings.current);
            break;
        case 2:
            display.println(F("Power:"));
            display.printf("%.1fW\n", readings.power);
            display.println(F("Energy:"));
            display.printf("%.1fWh", readings.energy);
            break;
        case 3:
            display.println(F("Freq:"));
            display.printf("%.1fHz\n", readings.frequency);
            display.println(F("PF:"));
            display.printf("%.2f", readings.pf);
            break;
    }
    
    display.display();
    currentPage = (currentPage % 3) + 1;
}

void displayError(const String& message) {  // Changed parameter type to String
    display.clearDisplay();
    display.setTextSize(1);
    display.setCursor(0, 20);
    display.println(message);
    display.display();
}

void handleOverloadProtection(const PowerReadings& readings) {
    if (readings.isOverload && !overloadDetected) {
        overloadDetected = true;
        relayState = false;
        digitalWrite(RELAY_PIN, LOW);
        displayError(String("OVERLOAD! Power Cut"));  // Convert to String
        sendAlert("Overload detected - Power cut off");
    } else if (!readings.isOverload && overloadDetected) {
        overloadDetected = false;
    }
}

void handleWatchdog() {
    if (!WiFi.isConnected() && deviceConfig.autoReconnect) {
        WiFi.reconnect();
    }
    
    float testVoltage = pzem.voltage();
    if (testVoltage < 0) {
        Serial.println(F("PZEM not responding - resetting"));
        ESP.restart();
    }
    
    lastWatchdogReset = millis();
}

void setupWebServer() {
    webServer.on("/", HTTP_GET, handleRoot);
    webServer.on("/data", HTTP_GET, handleData);
    webServer.on("/config", HTTP_POST, handleConfig);
    webServer.begin();
}

void handleRoot() {
    String html = F("<!DOCTYPE html><html><head>"
                   "<meta name='viewport' content='width=device-width, initial-scale=1'>"
                   "<title>Power Meter</title>"
                   "<style>body{font-family:Arial;margin:20px;}"
                   "table{border-collapse:collapse;width:100%}"
                   "td,th{border:1px solid #ddd;padding:8px;text-align:left}</style>"
                   "</head><body>"
                   "<h1>Power Meter Status</h1>"
                   "<div id='data'>Loading...</div>"
                   "<script>"
                   "function updateData(){"
                   "fetch('/data')"
                   ".then(r=>r.json())"
                   ".then(d=>{"
                   "let html='<table>';"
                   "for(let[k,v]of Object.entries(d)){"
                   "html+=`<tr><th>${k}</th><td>${v}</td></tr>`;"
                   "}"
                   "html+='</table>';"
                   "document.getElementById('data').innerHTML=html;"
                   "});"
                   "}"
                   "updateData();setInterval(updateData,1000);"
                   "</script></body></html>");
    webServer.send(200, "text/html", html);
}

void handleData() {
    PowerReadings readings = readPowerMeasurements();
    
    String json = "{\"Voltage (V)\":" + String(readings.voltage, 1) +
                 ",\"Current (A)\":" + String(readings.current, 2) +
                 ",\"Power (W)\":" + String(readings.power, 1) +
                 ",\"Energy (Wh)\":" + String(readings.energy, 1) +
                 ",\"Total Energy (Wh)\":" + String(totalEnergy, 1) +
                 ",\"Frequency (Hz)\":" + String(readings.frequency, 1) +
                 ",\"Power Factor\":" + String(readings.pf, 2) +
                 ",\"Relay State\":\"" + String(relayState ? "ON" : "OFF") + "\"" +
                 ",\"Status\":\"" + String(readings.isValid ? (readings.isOverload ? "OVERLOAD" : "OK") : "ERROR") + "\"}";
                 
    webServer.send(200, "application/json", json);
}

void handleConfig() {
    if (webServer.hasArg("currentLimit")) {
        float newLimit = webServer.arg("currentLimit").toFloat();
        if (newLimit > 0 && newLimit <= MAX_CURRENT) {
            deviceConfig.currentLimit = newLimit;
        }
    }
    
    if (webServer.hasArg("powerLimit")) {
        float newLimit = webServer.arg("powerLimit").toFloat();
        if (newLimit > 0 && newLimit <= MAX_POWER) {
            deviceConfig.powerLimit = newLimit;
        }
    }
    
    if (webServer.hasArg("autoReconnect")) {
        deviceConfig.autoReconnect = (webServer.arg("autoReconnect") == "1");
    }
    
    saveConfiguration();
    webServer.send(200, "text/plain", F("Configuration updated"));
}

void loadConfiguration() {
    EEPROM.get(EEPROM_CONFIG_ADDR, deviceConfig);
    
    // Validate config and set defaults if invalid
    if (isnan(deviceConfig.currentLimit) || 
        deviceConfig.currentLimit <= 0 || 
        deviceConfig.currentLimit > MAX_CURRENT) {
        deviceConfig.currentLimit = MAX_CURRENT;
    }
    
    if (isnan(deviceConfig.powerLimit) || 
        deviceConfig.powerLimit <= 0 || 
        deviceConfig.powerLimit > MAX_POWER) {
        deviceConfig.powerLimit = MAX_POWER;
    }
    
    // Load relay state
    EEPROM.get(EEPROM_RELAY_STATE_ADDR, relayState);
    if (relayState != true && relayState != false) {
        relayState = true;  // Default to on if invalid
    }
}

void saveConfiguration() {
    EEPROM.put(EEPROM_CONFIG_ADDR, deviceConfig);
    EEPROM.put(EEPROM_RELAY_STATE_ADDR, relayState);
    EEPROM.commit();
}

void sendAlert(const String& message) {  // Changed parameter type to String
    if (!WiFi.isConnected()) return;
    
    HTTPClient http;
    WiFiClientSecure client;
    client.setInsecure();
    
    String postData = "device_id=" + deviceId +
                     "&alert=" + message;
                     
    http.begin(client, serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    int httpResponseCode = http.POST(postData);
    http.end();
}

void checkRelayState() {
    if (!WiFi.isConnected()) return;
    if (millis() - lastServerUpdate < SERVER_UPDATE_MIN_INTERVAL) return;
    
    HTTPClient http;
    WiFiClientSecure client;
    client.setInsecure();
    
    String url = String(relayControlUrl) + "?device_id=" + deviceId;
    http.begin(client, url);
    
    int httpResponseCode = http.GET();
    if (httpResponseCode == 200) {
        String payload = http.getString();
        payload.trim();
        
        bool newState = (payload == 
        "1");
        if (newState != relayState) {
            relayState = newState;
            digitalWrite(RELAY_PIN, relayState ? HIGH : LOW);
            EEPROM.put(EEPROM_RELAY_STATE_ADDR, relayState);
            EEPROM.commit();
            
            display.clearDisplay();
            display.setTextSize(2);
            display.setCursor(0, 20);
            display.println(relayState ? F("Relay ON") : F("Relay OFF"));
            display.display();
            delay(1000);
        }
    }
    http.end();
    lastServerUpdate = millis();
}

// First, add the toggleRelayState function from V3
void toggleRelayState() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        WiFiClientSecure client;
        client.setInsecure();

        bool newState = !relayState;
        String postData = "device_id=" + deviceId + "&state=" + String(newState ? "1" : "0");

        http.begin(client, relayControlUrl);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");

        int httpResponseCode = http.POST(postData);
        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Server Response: " + response);
            
            if (response == "1" && newState != relayState) {
                relayState = newState;
                digitalWrite(RELAY_PIN, relayState ? HIGH : LOW);
                display.clearDisplay();
                display.setTextSize(2);
                display.setCursor(30, 30);
                display.println(relayState ? "ON" : "OFF");
                display.display();
                delay(2000);
                
                // Save relay state to EEPROM
                EEPROM.put(EEPROM_RELAY_STATE_ADDR, relayState);
                EEPROM.commit();
            }
        } else {
            Serial.print("Error code: ");
            Serial.println(httpResponseCode);
        }
        http.end();
    }
}

// Replace the switch handling code in loop() with this modified version
void handleSwitches() {
    static unsigned long lastSwitch1Press = 0;
    static unsigned long lastSwitch2Press = 0;

    if (!digitalRead(SWITCH1_PIN) && (millis() - lastSwitch1Press > DEBOUNCE_DELAY)) {
        lastSwitch1Press = millis();
        toggleRelayState();  // Use the V3 toggle function instead of direct toggle
    }

    if (!digitalRead(SWITCH2_PIN) && (millis() - lastSwitch2Press > DEBOUNCE_DELAY)) {
        lastSwitch2Press = millis();
        
        // WiFi reset functionality from V3
        WiFiManager wifiManager;
        wifiManager.resetSettings();
        
        display.clearDisplay();
        display.setCursor(0, 20);
        display.setTextSize(2);
        display.println("Connecting");
        display.setCursor(0, 40);
        display.println("to WiFi...");
        display.display();
        
        wifiManager.autoConnect("AutoConnectAP003");
        
        display.clearDisplay();
        display.setCursor(10, 20);
        display.setTextSize(2);
        display.println("Success!!");
        display.display();
        delay(2000);
    }
}

void setup() {
    Serial.begin(115200);
    EEPROM.begin(512);
    
    // Initialize display
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
        for (;;);
    }
    display.setTextColor(WHITE);
    display.clearDisplay();
    display.display();
    
    // Initialize pins
    pinMode(RELAY_PIN, OUTPUT);
    pinMode(SWITCH1_PIN, INPUT_PULLUP);
    pinMode(SWITCH2_PIN, INPUT_PULLUP);
    pinMode(LED_PIN, OUTPUT);
    
    // Load saved configuration
    loadConfiguration();
    
    // Set initial relay state
    digitalWrite(RELAY_PIN, relayState ? HIGH : LOW);
    
    // Initialize WiFi
    initializeWiFi();
    
    // Setup web server
    setupWebServer();
    
    // Reset energy if needed
    if (isnan(pzem.energy())) {
        pzem.resetEnergy();
    }
    
    // Load total energy from EEPROM
    EEPROM.get(EEPROM_ENERGY_ADDR, totalEnergy);
    if (isnan(totalEnergy)) totalEnergy = 0;
}

void loop() {
    static unsigned long lastDisplayUpdate = 0;
    static unsigned long lastDataSend = 0;
    
    // Handle switches (from V3)
    handleSwitches();
    
    // Handle web server
    webServer.handleClient();
    
    // Read power measurements
    PowerReadings readings = readPowerMeasurements();
    
    // Update display periodically
    if (millis() - lastDisplayUpdate >= DISPLAY_INTERVAL) {
        if (readings.isValid && !readings.isOverload) {
            updateDisplay(readings);
        } else {
            displayError(readings.errorMessage.c_str());
        }
        lastDisplayUpdate = millis();
    }
    
    // Send data to server periodically
    if (millis() - lastDataSend >= SEND_INTERVAL) {
        sendToServer(readings);
        lastDataSend = millis();
    }
    
    // Check relay control from server
    if (millis() - lastRelayCheck >= RELAY_CHECK_INTERVAL) {
        checkRelayState();
        lastRelayCheck = millis();
    }
    
    // Handle overload protection
    handleOverloadProtection(readings);
    
    // Save total energy periodically
    if (millis() - lastEepromSave >= EEPROM_SAVE_INTERVAL) {
        if (readings.isValid) {
            totalEnergy = readings.energy;
            EEPROM.put(EEPROM_ENERGY_ADDR, totalEnergy);
            EEPROM.commit();
        }
        lastEepromSave = millis();
    }
    
    // Handle watchdog
    if (millis() - lastWatchdogReset >= WATCHDOG_INTERVAL) {
        handleWatchdog();
    }
    
    // Indicate system is running
    digitalWrite(LED_PIN, (millis() / 1000) % 2);
}