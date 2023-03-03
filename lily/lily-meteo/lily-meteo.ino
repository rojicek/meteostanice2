#include <WiFi.h>
#include <HTTPClient.h>

#include <ESP32Time.h>
#include <ArduinoJson.h>

#include "config.h"
#include "time.h"


TTGOClass *ttgo;
const char *wifi_ap = "R_host";
const char *wifi_pd = "badenka5";

ESP32Time board_time(0);  //no offset, board is syncd to local


File myFile;

// change this to match your SD shield or module;
//     Arduino Ethernet shield: pin 4
//     Adafruit SD shields and modules: pin 10
//     Sparkfun SD shield: pin 8
const int chipSelect = 4;

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
  while (!Serial) {
    delay(100);
  }
  Serial.println("Serial initialized");

  ///////////////////////////////
  Serial.print("Initializing SD card...");
  if (!SD.begin(chipSelect)) {
    Serial.println("SD initialization failed!");
    return;
  }
  Serial.println("SD initialization done.");
  Serial.println("Creating example.txt...");
  myFile = SD.open("example.aaa", FILE_WRITE);
  myFile.close();
  if (SD.exists("example.aaa")) {
    Serial.println("obrazek existuje");
  } else {
    Serial.println("obrazek neexistuje");
  }


  /////////////////////////////////

  //nastav obrazovku
  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  int sync = 0;
  while (sync == 0) {
    // nastav presny cas
    if (wifi_connect() == 1) {  //wifi ok
      sync = 1;
      sync_local_clock();
      wifi_disconnect();
    } else {
      Serial.println("NEMAM REALNY CAS");
      delay(10000);
    }
  }

  ttgo->tft->fillScreen(BCK_COLOR);

  made_up();  //jen docasne

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

  return;  //skip all

  current_epoch = board_time.getEpoch();

  if (current_epoch > last_epoch_daily + 86400) {
    //do daily jobs
    if (wifi_connect() == 1) {  //kdyz se nepovede, tak nevadi, priste

      //udelej vsechno
      sync_local_clock();
      update_meteo();

      //todo: obnov HDO
      wifi_disconnect();
      last_epoch_daily = current_epoch;  //jen kdyz se to povedlo
      last_epoch_5min = current_epoch;   //udelal jsem najednou i 5min i pocasi
      Serial.println("daily jobs done");

    } else {
      Serial.println("Daily jobs failed, waiting 1 min");
      last_epoch_daily = last_epoch_daily + 60;  //posunu posledni uspech jen trochu abych to zkusil brzo znova
    }
  }

  if (current_epoch > last_epoch_5min + 300)  //300 //todo: nebo aspon 1 min a v 1. min po pulnoci
  {
    //do 5min jobs - pocasi a vsechno
    if (wifi_connect() == 1) {  //kdyz se nepovede, tak nevadi, priste

      update_meteo();


      wifi_disconnect();
      last_epoch_5min = current_epoch;

      Serial.println("5min jobs done");
    } else {
      last_epoch_5min = last_epoch_5min + 60;  //pockam jen minutu
      Serial.println("5min jobs failed, waiting 1 min");
    }
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
int wifi_connect() {

  Serial.print("wifi");

  int max_tries = 30;

  WiFi.begin(wifi_ap, wifi_pd);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
    max_tries--;
    if (max_tries < 0) {
      Serial.println("failed, giving up");
      return 0;
    }
  }
  Serial.println("ok");
  return 1;
}
void wifi_disconnect() {
  WiFi.disconnect();
  int max_tries = 30;

  while ((WiFi.status() != WL_DISCONNECTED) && (max_tries > 0)) {
    delay(500);
    max_tries--;
    Serial.print("+");
  }

  Serial.println(" wifi disconnected (or not:)");
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

  //Serial.println("made up");
}

void update_meteo() {
  //Serial.println("update meteo intro");
  StaticJsonDocument<1000> w_doc;
  HTTPClient http;
  String url = "https://www.rojicek.cz/meteo/meteo-query.php?pwd=pa1e2";

  //Serial.println("update meteo 1");

  http.begin(url.c_str());

  int httpResponseCode = http.GET();
  //Serial.println("Meteo response: " + String(httpResponseCode));
  if (httpResponseCode == 200) {
    String payload = http.getString();
    DeserializationError error = deserializeJson(w_doc, payload);

    if (error) {
      Serial.print(F("deserializeJson() failed: "));
      Serial.println(error.f_str());
      return;
    }


    //JsonArray weather = w_doc["weather"];
    const char *aqi = w_doc["weather"]["aqi"];
    const int temp = w_doc["weather"]["temp"];


    //Serial.println(w_doc);
    Serial.printf("aqi: %s\n", aqi);
    Serial.printf("temp: %d\n", temp);



    ttgo->tft->fillRect(200, 50, 50, 50, BCK_COLOR);
    ttgo->tft->setTextSize(4);
    ttgo->tft->setCursor(200, 50);
    ttgo->tft->setTextColor(BLACK);
    ttgo->tft->print(temp);
  } else {
    Serial.printf("Meteo update failed, next time, response [%d]\n", httpResponseCode);
  }

  //Serial.println("update meteo get ok");

  http.end();


  Serial.println("update meteo leaving");
}
