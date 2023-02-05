#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Wire.h>

#include <ArduinoJson.h>

ESP8266WiFiMulti WiFiMulti;

void setup() {
  Serial.begin(115200);
  while (!Serial) continue;

  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP("R_host", "badenka5");

  Serial.println("inicializace hotova");
  delay(1000);

}

void loop() {
  // put your main code here, to run repeatedly:
  // put your main code here, to run repeatedly:
  if (WiFiMulti.run() == WL_CONNECTED) {
    Serial.println("wifi ok");

    //zkusim parsovat weather
    std::unique_ptr<BearSSL::WiFiClientSecure> client(new BearSSL::WiFiClientSecure);
    client->setInsecure();
    HTTPClient https;

    //String url = "https://www.rojicek.cz/meteo/hdo-query.php";
    String url = "https://www.rojicek.cz/meteo/meteo-query.php?pwd=pa1e2";

    if (https.begin(*client, url)) 
    {
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

         ///////////////
         // The filter: it contains "true" for each value we want to keep
         //StaticJsonDocument<1000> filter;
         //filter["intervals"] = true;
         //filter["current"]["dt"] = true;

          // Deserialize the document
          StaticJsonDocument<1000> doc;
 
          DeserializationError error = deserializeJson(doc, payload);  //DeserializationOption::Filter(filter)
          if (error) {
            Serial.print(F("deserializeJson() failed: "));
            Serial.println(error.f_str());
            return;
          }

        
          // Print the result
          serializeJsonPretty(doc, Serial);
          Serial.println("-------------");


          ///////////////////
          //fuguje
        }
      } 
      else 
      {
        Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }

      https.end();



    } else {
      Serial.printf("[HTTPS] Unable to connect\n");
    }
  }
  delay(300000);
}
