#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Wire.h>

#include <ArduinoJson.h>

ESP8266WiFiMulti WiFiMulti;

StaticJsonDocument<3000> doc;

const char *  lat = "50.0973722";
const char *  lon = "14.4074581";
const char * API_key = "5cb11160e6649574aca2ee031feda8d4";

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);

  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP("R_host", "badenka5");

  Serial.println("inicializace hotova");
  delay(1000);
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

          Serial.println("NACTENA DATA");

          for (int i = 0; i < intervals.size(); i++) {
            const char* cas_zacatek = intervals[i][0];
            const char* cas_konec = intervals[i][1];
            Serial.printf("zacatek: %s\n", cas_zacatek);
            Serial.printf("konec: %s\n", cas_konec);
          }



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

    //pocasi (test)
    char url_weather[250] = "https://api.openweathermap.org/data/3.0/onecall?lat=";
    strcat(url_weather, lat);
    strcat(url_weather, "&lon=");
    strcat(url_weather, lon);
    strcat(url_weather, "&appid=");
    strcat(url_weather, API_key);
    strcat(url_weather, "&units=metric");

    Serial.printf("w url: %s\n", url_weather);

      if (https.begin(*client, url_weather)) {
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
          String w_payload = "--init--";
          w_payload = https.getString();
          Serial.println("payload pocasi:");
          Serial.println(w_payload);
          Serial.println("^^^");

///
  StaticJsonDocument<200> filter;
  filter["current"]["dt"] = true;
  
  // Deserialize the document
  StaticJsonDocument<800> w_doc;
  deserializeJson(w_doc, w_payload, DeserializationOption::Filter(filter));

  
  serializeJsonPretty(w_doc, Serial);

 

          /////////////////
          //fuguje
        }
      } else {
        Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }

      https.end();



    } else {
      Serial.printf("[HTTPS] Unable to connect\n");
    }


  } //konec wifi

  delay(10000);
}
