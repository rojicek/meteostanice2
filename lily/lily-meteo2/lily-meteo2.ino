#include "config.h"
//#include <EEPROM.h>

#include <WiFi.h>
#include <WiFiMulti.h>

WiFiMulti WiFiMulti;

TTGOClass *ttgo;
TFT_eSPI *tft;

#include "time.h"

const char *ntpServer = "pool.ntp.org";
const long gmtOffset_sec = 0;
const int daylightOffset_sec = 3600;

#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

int score;
struct tm timeinfo;
const unsigned int FORESTGREEN = COLOR565(34, 139, 34);  //forest green
const unsigned int BLACK = COLOR565(0, 0, 0);

// barvy
#define BCK_COLOR TFT_WHITE


void setup() {
  Serial.begin(115200);
  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  tft = ttgo->tft;

  tft->setRotation(1);

  // while (1) {
  //     Serial.println(ttgo->touched());
  // }

  tft->setTextFont(1);
  tft->fillScreen(BCK_COLOR);
  //tft->fillRect(0, 0, 310, 470, TFT_WHITE); ok - ukazuje dimenze! (bez rotace)

  WiFiMulti.addAP("R_host", "badenka5");

  Serial.println();
  Serial.println();
  Serial.print("Waiting for WiFi... ");

  while (WiFiMulti.run() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  delay(500);

  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);



  score = 0;
}

void loop() {
  // put your main code here, to run repeatedly:
  //Serial.println("ping");

  tft->setTextSize(3);
  tft->setCursor(20, 20);
  tft->setTextColor(BCK_COLOR);
  //tft->print(score);
  tft->print(asctime(&timeinfo));

  score++;
  
  if (!getLocalTime(&timeinfo)) {
    Serial.println("Failed to obtain time");
    return;
  }

  tft->setCursor(20, 20);
  tft->setTextColor(FORESTGREEN);
  //tft->print(score);
  tft->print(asctime(&timeinfo));

  


 // Serial.println(&timeinfo, "%A, %B %d %Y %H:%M:%S");

  Serial.println (asctime(&timeinfo));



  delay(1000);
}