#include "config.h"

TTGOClass *ttgo;

int i = 0;

void setup()
{
    Serial.begin(115200);
    Serial.println("init 1");

    //Get watch instance
    ttgo = TTGOClass::getWatch();

    Serial.println("init 2");

    // Initialize the hardware
    ttgo->begin();

    // Turn on the backlight
    ttgo->openBL();

    ttgo->lvgl_begin();

    Serial.println("init 3");

    ttgo->lvgl_whirling(1);

    Serial.println("init 4");

        //Check if RTC is online
    if (!ttgo->deviceProbe(0x51)) {
        Serial.println("RTC CHECK FAILED");
        ttgo->tft->fillScreen(TFT_BLACK);
        ttgo->tft->setTextFont(4);
        ttgo->tft->setCursor(0, 0);
        ttgo->tft->setTextColor(TFT_RED);
       // ttgo->tft->println("RTC CHECK FAILED");
        delay(5000);
    }

   // ttgo->tft->println("init konec");
    //ttgo->tft->fillScreen(TFT_RED);
    Serial.println("init done");

}

void loop()
{

  lv_task_handler();
   //ttgo->tft->println("ping");
   Serial.println("ping");
    if (i==0)
    {
   ttgo->tft->fillScreen(TFT_RED);
   i = 1;
    }
    else
    {
       ttgo->tft->fillScreen(TFT_BLUE);
   i = 0;
    }
    
  delay(1000);

}