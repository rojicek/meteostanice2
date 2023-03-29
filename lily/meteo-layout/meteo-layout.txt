// https://tomeko.net/online_tools/file_to_hex.php?lang=en
//NEPOUZIVAT SMOOTH

//https://www.mischianti.org/images-to-byte-array-online-converter-cpp-arduino/
//https://cloudconvert.com/svg-to-bmp
//http://www.rinkydinkelectronics.com/t_imageconverter565.php


//http://www.rinkydinkelectronics.com/t_imageconverter565.php - TOHLE pouzivam

//https://javl.github.io/image2cpp/

//fonty: postup - ... nefunguje :)
// vybrat ttf font: https://fonts.google.com/noto/specimen/Noto+Sans?noto.query=Noto+Sans
// nahrat na https://rop.nl/truetype2gfx/
// vybrat si velikost
// udelat h + pouzit jak tady
// kazda velikost je jiny font
// pridat #include <pgmspace.h>

//postup - asi ok
// processing - vybrat font a ulozit jako wlv
//https://wiki.seeedstudio.com/Wio-Terminal-LCD-Anti-aliased-Fonts/
//vlw prevest na byty: https://tomeko.net/online_tools/file_to_hex.php?lang=en

#include "config.h"
// #include "extra.h" obrazek primo v .h



#include <SPI.h>
#include <SD.h>


File picFile;



//#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

//ok#define IMGW 150
//ok#define IMGH 150

//#define IMGW 480
//#define IMGH 150
#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)
/////////////////////////////////////////////////////////////////////////////
/*
void drawPic(int x, int y, int h, int w, String pic) {
  //File picFile = SD.open("/layout/sunset.raw", FILE_READ);
  picFile = SD.open("/test.raw", FILE_READ);

  Serial.print("pic:");
  Serial.println(picFile);

  if (picFile) {
    for (int i = 0; i < 150; i++) {
      for (int j = 0; j < 150; j++) {

        char rgb1 = picFile.read();
        char rgb2 = picFile.read();

        drawPixel(j, i, 256 * rgb1 + rgb2);
      }
    }

    close(picFile);
  } else {
    Serial.println("cant read image");
    //Serial.println(pic);
  }
}
*/
void setup() {
  Serial.begin(115200);

  //SD karta
  if (!SD.begin(4)) {
    Serial.println("initialization failed!");
    while (1)
      ;  // bacha - tohle je nekonecna smycka
  }
  Serial.println("initialization done.");

  delay(2000);


  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  ttgo->tft->fillScreen(TFT_RED);
  /*
  ttgo->tft->loadFont(ubuntu_regular_18);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(15, 15);
  ttgo->tft->print("6:00");
*/
  //printText()
  //drawPic(15, 15, 35, 28, "/layout/sunset.raw");
  //drawPic(15, 15, 150, 150, "/layout/sunset.raw");



  //cas
  /*
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
  */
}



void loop() {

  picFile = SD.open("/test.raw", FILE_READ);
  // picFile = SD.open("/layout-top.raw", FILE_READ);


  if (picFile) {
    Serial.println("budu cist z karty");

    //int height = 480;
    //int width = 320;

    // int height = 150;
    //int width = 150;

    char rgb1;
    char rgb2;

    int barva;

    for (int i = 0; i < 150; i++) {


      for (int j = 0; j < 150; j++) {

        //drawPixel(j, i, TFT_BLACK);


        rgb1 = picFile.read();
        rgb2 = picFile.read();

        barva = 256 * rgb1 + rgb2;
        //Serial.print(i);
        //Serial.print("-");
        //Serial.println(j);
        //Serial.print("=");
        //Serial.println(256 * rgb1 + rgb2);

        drawPixel(j, i, barva);

        //if ((256 * rgb1 + rgb2) > 0) {
        /* if (barva < 65000) {
          drawPixel(j, i, TFT_BLACK);
        } else {
          drawPixel(j, i, TFT_WHITE);
        }*/
      }
    }
  }
  else
  {
    Serial.println("sd problem");
  }
}