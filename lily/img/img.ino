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

#include "config.h"
#include "extra.h"

#include "NotoSansBold15.h" //ok
#include "NotoSans_Medium20pt7b.h" //muj font


#define AA_FONT_SMALL NotoSansBold15
///#define AA_FONT_SMALL NotoSans_Medium20pt7bBitmaps


//#include "font1.h"
//#define GFXFF 1

//#define MYFONT32 &myFont32pt8b

//#include "Ubuntubold.c"
//#include "RREFont.h"

//RREFont font;

//#include "rre_ubuntu_32.h"

#include <SPI.h>
#include <SD.h>
//#include <Streaming.h>

//#define BUFFPIXEL 20

File picFile;


//#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

#define IMGW 150
#define IMGH 150

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

  delay(2000);

  // ttgo->tft->fillScreen(TFT_BLUE);

  // delay(2000);

  picFile = SD.open("/test.raw", FILE_READ);


  if (picFile) {
    Serial.println("budu cist z karty");

    int height = 150;
    int width = 150;

    for (int i = 0; i < height; i++) {
      for (int j = 0; j < width; j++) {
        char rgb1 = picFile.read();
        char rgb2 = picFile.read();

        drawPixel(j, i, 256 * rgb1 + rgb2);
      }
    }

    picFile.close();

    picFile.close();

  } else {
    Serial.println("soubor nenalezen ...?");
  }


  ttgo->tft->loadFont(AA_FONT_SMALL);
  ttgo->tft->setTextSize(2);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(200, 100);
  ttgo->tft->print("123 dvojka");

  ttgo->tft->setTextSize(3);
  ttgo->tft->setTextColor(TFT_BLACK);
  ttgo->tft->setCursor(200, 150);
  ttgo->tft->print("123 trojka");

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




  delay(2000);
}