#include <WiFi.h>
#include <HTTPClient.h>
/*
#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>

#include <WiFiClientSecureBearSSL.h>
*/

#include <ESP32Time.h>
#include <ArduinoJson.h>

#include "config.h"
#include "time.h"


TTGOClass *ttgo;
const char *wifi_ap = "R_host";
const char *wifi_pd = "badenka5";

ESP32Time board_time(0);  //no offset, board is syncd to local


#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

const unsigned int FORESTGREEN = COLOR565(34, 139, 34);
const unsigned int WHITE = COLOR565(255, 255, 255);
const unsigned int GREEN = COLOR565(0, 255, 0);
const unsigned int BLUE = COLOR565(0, 0, 255);
const unsigned int RED = COLOR565(255, 0, 0);
const unsigned int BLACK = COLOR565(0, 0, 0);

#define BCK_COLOR WHITE


///////////////
// globalni promenne - tisk
int teplota = 14;
//String aqi = "XX";

void setup() {
  Serial.begin(115200);

  //nastav obrazovku
  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  // nastav presny cas
  wifi_connect();

  sync_local_clock();

  wifi_disconnect();

  ttgo->tft->fillScreen(BCK_COLOR);

  made_up();

  Serial.println("inicializovano");
}

// smycky
// 1s ... cas
// 5min ... pocasi
// 1 den ... configTime (abych mel presny cas)
String actual_time("");
String shown_time("XX");

unsigned long last_epoch_daily = 0;
unsigned long last_epoch_5min = 0;
unsigned long current_epoch = 0;

void loop() {

  current_epoch = board_time.getEpoch();

  if (current_epoch > last_epoch_daily + 86400) {
    //do daily jobs

    sync_local_clock();
    //todo: obnov HDO
    Serial.println("daily jobs done");
    last_epoch_daily = current_epoch;
  }

  if (current_epoch > last_epoch_5min + 300)  //300 //todo: nebo aspon 1 min a v 1. min po pulnoci
  {
    //do 5min jobs - pocasi a vsechno
    update_meteo();

    Serial.println("5min jobs done");
    last_epoch_5min = current_epoch;
  }

  //with every change (time only)
  actual_time = board_time.getTime("%e %b, %R");
  if (actual_time != shown_time) {
    print_time(shown_time, actual_time);
    shown_time = actual_time;
  }


  // todo: asi odstranit
  // Serial.println(actual_time);

  //nejaky kratky cas
  delay(100);
}  //loop

///////////////////////
void wifi_connect() {
  Serial.print("wifi");
  WiFi.begin(wifi_ap, wifi_pd);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("ok");
}
void wifi_disconnect() {
  WiFi.disconnect();
  //asi nemusim cekat na odpojeni
  /* 
    while (WiFi.status() != WL_DISCONNECTED) {
        delay(100);
        Serial.println("+");
    }
  */
  Serial.println("wifi disconnected");
}

// Function that gets current epoch time
void sync_local_clock() {
  const char *ntpServer = "pool.ntp.org";
  const long gmtOffset_sec = 3600;
  const int daylightOffset_sec = 3600;

  //sync with NTP
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);

  time_t now;
  struct tm timeinfo;
  int count = 10;  //how many tries
  while (count > 0) {
    if (getLocalTime(&timeinfo)) {
      count = -1;
    } else {
      count--;  //try again
      Serial.print("T");
    }
  }
  time(&now);
  board_time.setTime(now);
  return;
}

void print_time(String shown_time, String actual_time) {
  //replace old time with background
  ttgo->tft->setTextSize(3);
  ttgo->tft->setCursor(230, 5);
  ttgo->tft->setTextColor(BCK_COLOR);
  ttgo->tft->print(shown_time);

  //new time
  ttgo->tft->setCursor(230, 5);
  ttgo->tft->setTextColor(BLACK);
  ttgo->tft->print(actual_time);

  return;
}

void made_up() {
  // ikona pocasi
  ttgo->tft->fillRect(10, 20, 150, 150, GREEN);

  //teplota
  ttgo->tft->setTextSize(4);
  ttgo->tft->setCursor(30, 200);
  ttgo->tft->setTextColor(BLACK);
  String temp_now = String(teplota) + " C";
  //temp_now = temp_now + " C";

  ttgo->tft->print(temp_now);
  ttgo->tft->setTextSize(2);
  ttgo->tft->setCursor(170, 200);
  ttgo->tft->setTextColor(BLACK);
  ttgo->tft->print("12 C");

  Serial.println("made up");
}

void update_meteo() {
  //Serial.println("update meteo intro");
  StaticJsonDocument<500> w_doc;
  HTTPClient http;
  String url = "https://www.rojicek.cz/meteo/meteo-query.php?pwd=pa1e2";

  //Serial.println("update meteo 1");
  wifi_connect();
  //Serial.println("update meteo wifi ok");
  http.begin(url.c_str());

  int httpResponseCode = http.GET();
  if (httpResponseCode == 200) {
    String payload = http.getString();
    DeserializationError error = deserializeJson(w_doc, payload);

    //JsonArray weather = w_doc["weather"];
    const char *aqi = w_doc["weather"]["aqi"];
    //Serial.println(w_doc);
    Serial.print("aqi: ");
    Serial.println(aqi);


    ttgo->tft->fillRect(200, 50, 50, 50, BCK_COLOR);
    ttgo->tft->setTextSize(4);
    ttgo->tft->setCursor(200, 50);
    ttgo->tft->setTextColor(BLACK);
    ttgo->tft->print(aqi);
  }

  //Serial.println("update meteo get ok");
  Serial.println("Response: " + String(httpResponseCode));
  http.end();

  //Serial.println("update meteo get end");
  wifi_disconnect();
  Serial.println("update meteo leaving");
}
