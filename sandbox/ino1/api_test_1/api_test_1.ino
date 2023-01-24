#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Wire.h>

#include <ArduinoJson.h>

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

StaticJsonDocument<768> doc;

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

         

          DeserializationError error = deserializeJson(doc, payload);

          if (error) {
            Serial.print(F("deserializeJson() failed: "));
            Serial.println(error.f_str());
            return;
          }

          JsonArray intervals = doc["intervals"];

          const char* intervals_0_0 = intervals[0][0];  // "2023-01-24 22:20:00"
          const char* intervals_0_1 = intervals[0][1];  // "2023-01-24 23:00:00"

          const char* intervals_1_0 = intervals[1][0];  // "2023-01-25 02:40:00"
          const char* intervals_1_1 = intervals[1][1];  // "2023-01-25 03:20:00"

          const char* intervals_2_0 = intervals[2][0];  // "2023-01-25 07:20:00"
          const char* intervals_2_1 = intervals[2][1];  // "2023-01-25 08:00:00"

          const char* intervals_3_0 = intervals[3][0];  // "2023-01-25 11:20:00"
          const char* intervals_3_1 = intervals[3][1];  // "2023-01-25 12:00:00"

          const char* intervals_4_0 = intervals[4][0];  // "2023-01-25 15:00:00"
          const char* intervals_4_1 = intervals[4][1];  // "2023-01-25 15:40:00"

          const char* intervals_5_0 = intervals[5][0];  // "2023-01-25 19:20:00"
          const char* intervals_5_1 = intervals[5][1];  // "2023-01-25 20:00:00"

          const char* intervals_6_0 = intervals[6][0];  // "2023-01-25 22:20:00"
          const char* intervals_6_1 = intervals[6][1];  // "2023-01-25 23:00:00"

          Serial.println ("NACTENA DATA");
          Serial.printf("posledni: %s\n", intervals_6_1);

          ///////////////////
          //fuguje          
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
