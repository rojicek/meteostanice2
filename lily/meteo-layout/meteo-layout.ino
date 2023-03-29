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

  Serial.print("pic: ");
  Serial.println(picFile);

  if (picFile) {
    for (int i = x; i < x + h; i++) {
      for (int j = y; j < y + w; j++) {

        char rgb1 = picFile.read();
        char rgb2 = picFile.read();

        drawPixel(j, i, 256 * rgb1 + rgb2);
      }
    }

    close(picFile);
    Serial.println("closing image");

  } else {
    Serial.println("cant read image");
    //Serial.println(pic);
  }
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
 ttgo->tft->fillScreen(TFT_WHITE);
  int x = random(0, 50);
  int y = random(0, 50);
  Serial.println("ping 1");
  drawPic(0, 20, 150, 150, "eee");
  Serial.println("ping 2");

  drawPic(0, 0, 150, 150, "eee");



  delay(500000);
}