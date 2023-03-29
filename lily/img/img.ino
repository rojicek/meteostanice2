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

#include "NotoSansBold15.h"  //ok
#include "NotoSansBold36.h"

#include "mujfont1.h"
#include "mujfont2.h"



#define AA_FONT_SMALL NotoSansBold15
//#define MUJFONT1 NotoSansBold36 //ok
#define MUJFONT1 mujfont1
#define MUJFONT2 mujfont2
//mujfont1
//#define AA_FONT_SMALL NotoSans_Medium20pt7bBitmaps
//#define MYFONT &FreeSans20pt7bBitmaps

//#include "font1.h"
//#define GFXFF 1

#define MYFONT32 &myFont32pt8b

//#include "Ubuntubold.c"
//#include "RREFont.h"

//RREFont font;

//#include "rre_ubuntu_32.h"

#include <SPI.h>
#include <SD.h>
//#include <Streaming.h>

//#define BUFFPIXEL 20

File picFile;
File picFile2;


//#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

//ok#define IMGW 150
//ok#define IMGH 150

//#define IMGW 480
//#define IMGH 150

void setup() {
  Serial.begin(115200);

  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  ttgo->tft->fillScreen(TFT_WHITE);

  //SD karta
  if (!SD.begin(4)) {
    Serial.println("initialization failed!");
    while (1)
      ;  // bacha - tohle je nekonecna smycka
  }
  Serial.println("initialization done.");


  Serial.println("setup hotov");
}

void loop() {
  /*
  for (int x = 0; x < IMGW; x++)
    for (int y = 0; y < IMGH; y++) {
      //plot
      drawPixel(x, y, slunicko[x + y * IMGW]);
    }

  delay(2000);

  for (int x = 0; x < IMGW; x++)
    for (int y = 0; y < IMGH; y++) {
      //plot
      drawPixel(x, y, mrak[x + y * IMGW]);
    }

  delay(2000);
*/
  //ttgo->tft->fillScreen(TFT_WHITE);

  // delay(2000);

  // ttgo->tft->fillScreen(TFT_BLUE);

  // delay(2000);

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
      Serial.print("radek:");
      Serial.println(i);

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

    // picFile.close();

    picFile.close();
    Serial.println("soubor uzavren");

  } else {
    Serial.println("soubor nenalezen ...?");
  }

  Serial.println("konec");
  //////////////////////////////////////////////
/*
  picFile2 = SD.open("/layout-bottom.raw", FILE_READ);


  if (picFile2) {
    Serial.println("budu cist z karty 2");

    //int height = 480;
    //int width = 320;

    // int height = 150;
    //int width = 150;

    char rgb1;
    char rgb2;

    int barva;

    for (int i = 160; i < 320; i++) {
      Serial.println(i);

      for (int j = 0; j < 480; j++) {

        //drawPixel(j, i, TFT_BLACK);


        rgb1 = picFile2.read();
        rgb2 = picFile2.read();

        barva = 256 * rgb1 + rgb2;
        //Serial.print(i);
        //Serial.print("-");
        //Serial.println(j);
        //Serial.print("=");
        //Serial.println(256 * rgb1 + rgb2);

        //drawPixel(j, i, barva);

        //if ((256 * rgb1 + rgb2) > 0) {
        if (barva < 65000) {
          drawPixel(j, i, TFT_BLACK);
        } else {
          drawPixel(j, i, TFT_WHITE);
        }
      }
    }

    // picFile.close();

    picFile2.close();
    Serial.println("soubor 2 uzavren");

  } else {
    Serial.println("soubor 2  nenalezen ...?");
  }
*/
  Serial.println("konec");


  //////////////////////////////////////////////
  /*
  ttgo->tft->loadFont(AA_FONT_SMALL);  // ok pro NotoSansBold15

  ttgo->tft->setTextSize(2);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(200, 100);
  ttgo->tft->print("123 dvojka");

  ////////////
  ttgo->tft->loadFont(MUJFONT1);
  //ttgo->tft->setTextSize(3);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(200, 150);
  ttgo->tft->print("EXTRA font 20");

  ttgo->tft->loadFont(MUJFONT2);
  //ttgo->tft->setTextSize(3);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(200, 200);
  ttgo->tft->print("EXTRA font 40");
*/
  /*
  ttgo->tft->loadFont("_NSBold15.vlw");
  ttgo->tft->setTextSize(4);
  ttgo->tft->setTextColor(TFT_GREEN);
  ttgo->tft->drawString("Meteorologicka stanice", 10, 250);
*/
  //font.setFont(&rre_ubuntu_32);
  // font.setCR(0);
  //font.setColor(TFT_BLUE);
  // font.printStr(0, font.getHeight() * 0, name);
  // font.printStr(0, font.getHeight() * 1, "0123456789:;.-+*()!?/");

  //ttgo->tft->setFreeFont(MYFONT32);  // Select the font
  //ttgo->tft->setTextSize(3);
  //ttgo->tft->setCursor(200, 250);
  //ttgo->tft->setTextColor(TFT_BLACK);
  //ttgo->tft->print("123 dvojka");
  /*
  ttgo->tft->setTextSize(5);
  ttgo->tft->setCursor(200, 100);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->print("Ahoj 6C");

  ttgo->tft->setTextSize(4);
  ttgo->tft->setCursor(200, 150);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->print("ctyrka");

  ttgo->tft->setTextSize(3);
  ttgo->tft->setCursor(200, 200);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->print("trojka");


  ttgo->tft->setTextSize(2);
  ttgo->tft->setCursor(200, 250);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->print("dvojka");
*/




  delay(20000);
}