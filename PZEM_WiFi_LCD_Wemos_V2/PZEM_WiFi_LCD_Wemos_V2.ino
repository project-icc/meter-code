#include <PZEM004Tv30.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFiManager.h>
#include <SoftwareSerial.h>

// ข้อมูล URL ของ PHP script
const char* serverUrl = "https://www.meter.it-icc.com/nanoConnect.php";

// Define RX and TX pins for SoftwareSerial
#define RX_PIN D5
#define TX_PIN D6

SoftwareSerial pzemSerial(RX_PIN, TX_PIN); // Create SoftwareSerial object
PZEM004Tv30 pzem(pzemSerial); // Use SoftwareSerial object for PZEM004Tv30

// ข้อมูลการเชื่อมต่อจอ OLED
#define SCREEN_WIDTH 128 // ความกว้างของจอ OLED
#define SCREEN_HEIGHT 64 // ความสูงของจอ OLED
#define OLED_RESET    -1 // ไม่มีการรีเซ็ตพิน
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
#define RELAY_PIN D0
#define SWITCH1_PIN D7
#define SWITCH2_PIN D3


int currentPage = 1;
bool relayState = true;
unsigned long lastPageSwitch = 0;
const unsigned long pageSwitchInterval = 3000; // 3 วินาที

void setup() {
    Serial.begin(115200);
    Serial.println("PZEM-004T Test");

    pinMode(RELAY_PIN, OUTPUT);
    pinMode(SWITCH1_PIN, INPUT_PULLUP);
    pinMode(SWITCH2_PIN, INPUT_PULLUP);

    // เริ่มต้นการใช้งานจอ OLED
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
        for (;;);
    }
    display.clearDisplay();
    display.setTextSize(1);
    display.setTextColor(SSD1306_WHITE);

    display.clearDisplay();
    display.setCursor(10, 20);
    display.setTextSize(1);
    display.println("IP:");
    display.println("192.168.4.1");
    display.display();
    delay(3000); // แสดงข้อความ Connected to WiFi เป็นเวลา 2 วินาที

    // เริ่มต้น WiFi Manager
    WiFiManager wifiManager;
    wifiManager.autoConnect("AutoConnectAP");

    display.clearDisplay();
    display.setCursor(10, 20);
    display.setTextSize(2);
    display.println("Success!!");
    display.display();
    delay(2000); // แสดงข้อความ Connected to WiFi เป็นเวลา 2 วินาที
}

void loop() {
    handleSwitches();

    // Read data from PZEM
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    float frequency = pzem.frequency();
    float pf = pzem.pf();

    // Debug print to check if data is read correctly
    Serial.print("Voltage: "); Serial.print(voltage); Serial.println(" V");
    Serial.print("Current: "); Serial.print(current); Serial.println(" A");
    Serial.print("Power: "); Serial.print(power); Serial.println(" W");
    Serial.print("Energy: "); Serial.print(energy); Serial.println(" kWh");
    Serial.print("Frequency: "); Serial.print(frequency); Serial.println(" Hz");
    Serial.print("Power Factor: "); Serial.print(pf); Serial.println();

    if (millis() - lastPageSwitch >= pageSwitchInterval) {
        currentPage = (currentPage % 3) + 1;
        lastPageSwitch = millis();
    }

    displayData(voltage, current, power, energy, frequency, pf);

    if (WiFi.status() == WL_CONNECTED) {
        sendToServer(voltage, current, power, energy, frequency, pf);
    }

    delay(10000); // ส่งข้อมูลทุกๆ 10 วินาที
}

void displayData(float voltage, float current, float power, float energy, float frequency, float pf) {
    display.clearDisplay();
    display.setCursor(0, 0);
    display.setTextSize(1);
    
    switch (currentPage) {
        case 1:
            display.setTextSize(2);
            display.println("Voltage:"); display.print(voltage); display.println(" V");
            display.println("Current:"); display.print(current); display.println(" A");
            break;
        case 2:
            display.setTextSize(2);
            display.println("Power:"); display.print(power); display.println(" W");
            display.println("Energy:"); display.print(energy); display.println(" Wh");
            break;
        case 3:
            display.setTextSize(2);
            display.println("Frequency:"); display.print(frequency); display.println(" Hz");
            display.println("PF:"); display.println(pf);
            break;
    }
    display.display();
}

void handleSwitches() {
    static unsigned long lastSwitch1Press = 0;
    const unsigned long debounceDelay = 50;

    if (digitalRead(SWITCH1_PIN) == LOW && millis() - lastSwitch1Press > debounceDelay) {
      lastSwitch1Press = millis();  // Update the last press time for debounce
      if (digitalRead(RELAY_PIN) == LOW) {
        digitalWrite(RELAY_PIN, HIGH);
        display.clearDisplay();
        display.setTextSize(2);
        display.setCursor(30, 30);
        display.println("ON");
        display.display();
        delay(5000);
        return;
      } 
      if (digitalRead(RELAY_PIN) == HIGH) {
        digitalWrite(RELAY_PIN, LOW);
        display.clearDisplay();
        display.setTextSize(2);
        display.setCursor(30, 30);
        display.println("OFF");
        display.display();
        delay(5000);
        return;
      }
    }


    if (digitalRead(SWITCH2_PIN) == LOW) {
        WiFiManager wifiManager;
        wifiManager.resetSettings();
        display.clearDisplay();
        display.setCursor(0, 20);
        display.setTextSize(2);
        display.println("Connecting");
        display.setCursor(0, 40);
        display.setTextSize(2);
        display.println("to WiFi...");
        display.display();
        wifiManager.autoConnect("AutoConnectAP");
        display.clearDisplay();
        display.setCursor(10, 20);
        display.setTextSize(2);
        display.println("Success!!");
        display.display();
        delay(2000);
    }
}

void sendToServer(float voltage, float current, float power, float energy, float frequency, float pf) {
    HTTPClient http;
    WiFiClientSecure client;
    client.setInsecure(); // สำหรับการทดสอบ ปิดการตรวจสอบใบรับรอง

    // สร้างข้อมูล POST string
    String postData = "voltage=" + String(voltage, 2) +
                      "&current=" + String(current, 2) +
                      "&power=" + String(power, 2) +
                      "&energy=" + String(energy, 2) +
                      "&frequency=" + String(frequency, 2) +
                      "&power_factor=" + String(pf, 2);

    Serial.println("กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์:");
    Serial.println(postData);

    // เริ่มการเชื่อมต่อ HTTP
    http.begin(client, serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpResponseCode = http.POST(postData); // เรียกใช้ POST request

    if (httpResponseCode > 0) {
        Serial.print("รหัสการตอบกลับ HTTP: ");
        Serial.println(httpResponseCode);

        // อ่านการตอบกลับจากเซิร์ฟเวอร์
        String response = http.getString();
        Serial.println("การตอบกลับจากเซิร์ฟเวอร์:");
        Serial.println(response);
    } else {
        Serial.print("รหัสข้อผิดพลาด: ");
        Serial.println(httpResponseCode);
    }

    // ยกเลิกการเชื่อมต่อ
    http.end();
}


