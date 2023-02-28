#include <WiFi.h>
#include <ESP32Time.h>

#include "config.h"
#include "time.h"



TTGOClass *ttgo;
const char *wifi_ap = "R_host";
const char *wifi_pd = "badenka5";

struct tm aktualni_cas;
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

void loop() {
  
  actual_time = board_time.getTime();
  if (actual_time != shown_time)
  {
  ttgo->tft->setTextSize(2);
  ttgo->tft->setCursor(20, 20);
  ttgo->tft->setTextColor(BCK_COLOR);
  ttgo->tft->print(shown_time);

  ttgo->tft->setCursor(20, 20);
  ttgo->tft->setTextColor(FORESTGREEN);
  ttgo->tft->print(actual_time);

  shown_time = actual_time;
  }

  Serial.println(board_time.getTime());


  delay(1000);
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
