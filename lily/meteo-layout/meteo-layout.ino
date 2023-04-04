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

void drawPic(int x, int y, int h, int w, String pic) {
  //File picFile = SD.open("/layout/sunset.raw", FILE_READ);
  picFile = SD.open("/test.raw", FILE_READ);
  char wpic[22500];
  picFile.readBytes(wpic, 22500);
  close(picFile);

  x = (int)random(0, 300);
  y = (int)random(0, 150);

  w = 150;
  h = 150;

  int c;
  if (random(2) < 1) {
    c = TFT_RED;
  } else {
    c = TFT_GREEN;
  }

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      drawPixel(i, j, c);
    }
  }

  //char rgb1, rgb2;

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      int ix = 2*(150*i+j);

      //drawPixel(i, j, 256 * wpic[ix] + wpic[ix+1]);

      // rgb1 = picFile.readBytes();
      // rgb2 = picFile.readBytes();

      //drawPixel(i, j, 256 * rgb1 + rgb2);
    }
  }


  /*
  Serial.print("pic: ");
  Serial.println(picFile);

  char rgb1, rgb2;

  if (picFile) {
    for (int i = 0; i < 150; i++) {
      for (int j = 50; j < 150 + 50; j++) {  //radek

        rgb1 = picFile.read();
        rgb2 = picFile.read();

        drawPixel(j, i, 256 * rgb1 + rgb2);
      }
    }

    close(picFile);
    Serial.println("closing image");

  } else {
    Serial.println("cant read image");
    //Serial.println(pic);
  }*/
}


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

  delay(1000);
  //drawPic(0, 0, 150, 150, "eee");

  Serial.println("setup hotov");
  delay(3000);
}

void loop() {

  //ttgo->tft->fillScreen(TFT_WHITE);
  //delay(1000);
  //ttgo->tft->fillScreen(TFT_BLUE);

  //int x = random(0, 50);
  //int y = random(0, 50);
  Serial.println("ping 1");
  //drawPic(50, 0, 150, 150, "eee");
  //drawPic(10, 10, 150, 150, "eee");

  drawPixel(5, 10, TFT_WHITE);
  drawPixel(12, 10, TFT_WHITE);
  drawPixel(100, 10, TFT_WHITE);
  drawPixel(400, 10, TFT_WHITE);
  drawPixel(475, 10, TFT_WHITE);

  drawPixel(5, 315, TFT_WHITE);

  drawPixel(475, 315, TFT_GREEN);

  int x;
  int y;

  for (x = 100; x < 120; x++) {
    //Serial.println(x);
    drawPixel(x, 50, TFT_GREEN);
  }

  for (x = 100; x < 120; x++) {
    // Serial.println(x);
    drawPixel(x, 100, TFT_GREEN);
  }

  for (y = 0; y < 50; y++) {
    //Serial.println(y);
    drawPixel(10, y, TFT_YELLOW);
  }

  for (y = 0; y < 320; y++) {
    //Serial.println(y);
    drawPixel(200, y, TFT_WHITE);
  }

  for (y = 0; y < 320; y++) {
    //Serial.println(y);
    drawPixel(477, y, TFT_GREEN);
    drawPixel(479, y, TFT_WHITE);
  }

  //drawPixel(1, 0, TFT_RED);
  //drawPixel(0, 1, TFT_RED);
  //drawPixel(1, 1, TFT_RED);

  Serial.println("ping 2");

  drawPic(50, 10, 150, 150, "to je jedno");

  delay(3000);

  //drawPic(300, 0, 150, 150, "eee");

  Serial.println("ping 3");



  // delay(500000);
}