// https://tomeko.net/online_tools/file_to_hex.php?lang=en
//NEPOUZIVAT SMOOTH

#include <SPI.h>
#include <SD.h>

#include "config.h"

//fonty
#include "ubuntu_reg.h"
#include "ubuntu_bold.h"

//#include "ubuntu_light.h "

TTGOClass* ttgo;

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


void drawRect(int x, int y, int w, int h, unsigned int c) {
  for (int ix = x; ix < x + w; ix++)
    for (int iy = y; iy < y + h; iy++) {
      drawPixel(ix, iy, c);
    }
}

void setup() {
  Serial.begin(115200);

  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  ttgo->tft->fillScreen(TFT_WHITE);

  //umisti bloky a texty
  //meteoikona
  drawRect(30, 10, 150, 150, TFT_RED);

  //cas
  ttgo->tft->loadFont(ubuntu_reg_25);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(300, 10);
  ttgo->tft->print("Aug 29, 18:58");

  ttgo->tft->loadFont(ubuntu_bold_45);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(10, 170);
  ttgo->tft->print("15°C");

  ttgo->tft->loadFont(ubuntu_reg_30);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(120, 180);
  ttgo->tft->print("25°C");
}



void loop() {}