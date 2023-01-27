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
/*
  //const __FlashStringHelper*
  String input_json = "{\"cod\":\"200\",\"message\":0,\"list\":[{\"dt\":1581498000,\"main\":{"
                      "\"temp\":3.23,\"feels_like\":-3.63,\"temp_min\":3.23,\"temp_max\":4.62,"
                      "\"pressure\":1014,\"sea_level\":1014,\"grnd_level\":1010,\"humidity\":"
                      "58,\"temp_kf\":-1.39},\"weather\":[{\"id\":800,\"main\":\"Clear\","
                      "\"description\":\"clear "
                      "sky\",\"icon\":\"01d\"}],\"clouds\":{\"all\":0},\"wind\":{\"speed\":6."
                      "19,\"deg\":266},\"sys\":{\"pod\":\"d\"},\"dt_txt\":\"2020-02-12 "
                      "09:00:00\"},{\"dt\":1581508800,\"main\":{\"temp\":6.09,\"feels_like\":-"
                      "1.07,\"temp_min\":6.09,\"temp_max\":7.13,\"pressure\":1015,\"sea_"
                      "level\":1015,\"grnd_level\":1011,\"humidity\":48,\"temp_kf\":-1.04},"
                      "\"weather\":[{\"id\":800,\"main\":\"Clear\",\"description\":\"clear "
                      "sky\",\"icon\":\"01d\"}],\"clouds\":{\"all\":9},\"wind\":{\"speed\":6."
                      "64,\"deg\":268},\"sys\":{\"pod\":\"d\"},\"dt_txt\":\"2020-02-12 "
                      "12:00:00\"}],\"city\":{\"id\":2643743,\"name\":\"London\",\"coord\":{"
                      "\"lat\":51.5085,\"lon\":-0.1257},\"country\":\"GB\",\"population\":"
                      "1000000,\"timezone\":0,\"sunrise\":1581492085,\"sunset\":1581527294}}";

  // The filter: it contains "true" for each value we want to keep
  StaticJsonDocument<200> filter;
  filter["list"][0]["dt"] = true;
  filter["list"][0]["main"]["temp"] = true;

  // Deserialize the document
  StaticJsonDocument<400> doc;
  Serial.println("-------------");
  deserializeJson(doc, input_json, DeserializationOption::Filter(filter));
  Serial.println("-------------");

  // Print the result
  //serializeJsonPretty(doc, Serial);
  */
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
    String url = "https://api.openweathermap.org/data/3.0/onecall?lat=50.0973722&lon=14.4074581&appid=5cb11160e6649574aca2ee031feda8d4&units=metric&exclude=minutely,hourly";

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
          //Serial.println(payload);

         ///////////////
         // The filter: it contains "true" for each value we want to keep
         StaticJsonDocument<100> filter;
         //filter["intervals"] = true;
         filter["current"]["dt"] = true;

          // Deserialize the document
          StaticJsonDocument<1000> doc;
 
          DeserializationError error = deserializeJson(doc, payload, DeserializationOption::Filter(filter));  //DeserializationOption::Filter(filter)
          if (error) {
            Serial.print(F("deserializeJson() failed: "));
            Serial.println(error.f_str());
            return;
          }

          Serial.println("-------------");

          // Print the result
          serializeJsonPretty(doc, Serial);
          Serial.println("-------------");


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
  delay(60000);
}
