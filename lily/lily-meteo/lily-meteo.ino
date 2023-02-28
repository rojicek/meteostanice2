#include <WiFi.h>
#include <ESP32Time.h>

#include "config.h"
#include "time.h"



TTGOClass *ttgo;
const char *wifi_ap = "R_host";
const char *wifi_pd = "badenka5";

//struct tm aktualni_cas;
//struct tm zobrazeny_cas;
ESP32Time board_time(0); //no offset, board is syncd to local


#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

const unsigned int FORESTGREEN = COLOR565(34, 139, 34); 
const unsigned int WHITE = COLOR565(255, 255, 255);

#define BCK_COLOR WHITE


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

  Serial.print("inicializovano");
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

  if (current_epoch > last_epoch_daily + 86400)
  {
    //do daily jobs
    Serial.println("daily jobs");
    sync_local_clock();
    //todo: obnov HDO
    last_epoch_daily = current_epoch;
  }

  if (current_epoch > last_epoch_5min + 300) //todo: nebo aspon 1 min a v 1. min po pulnoci
  {
    //do 5min jobs - pocasi a vsechno
    Serial.println("5min jobs");
    last_epoch_5min = current_epoch;
  }

  //with every change (time only)
  actual_time = board_time.getTime("%e %b, %R");
  if (actual_time != shown_time)
  {
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
  WiFi.begin(wifi_ap, wifi_pd);
  while (WiFi.status() != WL_CONNECTED) {
    delay(200);
    Serial.print(".");
  }
  Serial.println("");
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

void print_time (String shown_time, String actual_time)
{
  //replace old time with background
  ttgo->tft->setTextSize(2);
  ttgo->tft->setCursor(320, 5);
  ttgo->tft->setTextColor(BCK_COLOR);
  ttgo->tft->print(shown_time);

  //new time
  ttgo->tft->setCursor(320, 5);
  ttgo->tft->setTextColor(FORESTGREEN);
  ttgo->tft->print(actual_time);

  return;
}
