#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Wire.h>

ESP8266WiFiMulti WiFiMulti;

#define HTDU21D_ADDRESS 0x40  //Unshifted 7-bit I2C address for the sensor

#define TRIGGER_TEMP_MEASURE_HOLD 0xE3
#define TRIGGER_HUMD_MEASURE_HOLD 0xE5
#define TRIGGER_TEMP_MEASURE_NOHOLD 0xF3
#define TRIGGER_HUMD_MEASURE_NOHOLD 0xF5
#define WRITE_USER_REG 0xE6
#define READ_USER_REG 0xE7
#define SOFT_RESET 0xFE

byte sensorStatus;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);

  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP("R_host", "badenka5");

  Serial.println("inicializace hotova");
}

void loop() {
  // put your main code here, to run repeatedly:
  if (WiFiMulti.run() == WL_CONNECTED) {
    Serial.println("wifi ok");

    //zkusim parsovat weather
    std::unique_ptr<BearSSL::WiFiClientSecure> client(new BearSSL::WiFiClientSecure);
    client->setInsecure();
    HTTPClient https;

    String url = "https://www.rojicek.cz/meteo/hdo-query.php";

    if (https.begin(*client, url)) {
      // HTTPS
      Serial.print("[HTTPS] GET...\n");

      // start connection and send HTTP header
      int httpCode = https.GET();

      // httpCode will be negative on error
      if (httpCode > 0) {
        // HTTP header has been send and Server response header has been handled
        Serial.printf("[HTTPS] GET... code: %d\n", httpCode);

        // file found at server
        if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
          String payload = https.getString();
          Serial.println(payload);
        }
      } else {
        Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }

      https.end();
    } else {
      Serial.printf("[HTTPS] Unable to connect\n");
    }
  }

  delay(500000);
  Serial.println("ping");
}
