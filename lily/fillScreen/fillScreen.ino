#include "config.h"

#include <WiFi.h>
//#include <WiFiMulti.h>

//WiFiMulti WiFiMulti;

TTGOClass *ttgo;

void setup() {
  Serial.begin(115200);
  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();

  //WiFiMulti.addAP("R_host", "badenka5");

  Serial.println();
  Serial.println();
  Serial.print("Waiting for WiFi... ");

 



/*
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  */
}

void loop() {

    WiFi.begin("R_host", "badenka5");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println(WiFi.localIP());
    //Serial.println(WiFi.localIPv6());

    delay(3000); //nactu co je potreba
    
    WiFi.disconnect(); 
    while (WiFi.status() != WL_DISCONNECTED) {
        delay(100);
        Serial.println("*");
    }
    Serial.println("odpojeno");

    



  Serial.println("R");
  ttgo->tft->fillScreen(TFT_RED);
  delay(1000);
  Serial.println("G");
  ttgo->tft->fillScreen(TFT_GREEN);
  delay(1000);
  Serial.println("B");
  ttgo->tft->fillScreen(TFT_BLUE);
  delay(1000);

  Serial.println("bila");
  ttgo->tft->fillScreen(TFT_WHITE);
  delay(30000);
}
