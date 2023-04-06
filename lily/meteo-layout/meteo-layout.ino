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
//#include <FS.h>

#include "ubuntu_fonts.h"


File picFile;
File picFile2;


//#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

void drawPic(int x, int y, int h, int w, String pic) {
  //File picFile = SD.open("/layout/sunset.raw", FILE_READ);
  Serial.println("draw 1");

  //picFile = SD.open("/test.raw");
  picFile = SD.open(pic);
  Serial.println("draw 2");

  //uint8_t wpic[45000];
  uint8_t* pbuffer = nullptr;
  pbuffer = (uint8_t*)malloc(45000);
  Serial.println("draw 3");

  if (picFile) {
    Serial.print("trying..:");
    picFile.read(pbuffer, 45000);
    Serial.println("read!");
  }

  Serial.print("picFile:");
  Serial.println(picFile);
  close(picFile);

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      int ix = 2 * (150 * (i - x) + (j - y));  //counter

      drawPixel(i, j, 256 * pbuffer[ix] + pbuffer[ix + 1]);
    }
  }
  if (pbuffer) free(pbuffer);
}

SPIClass* sdhander = nullptr;

#define SD_CS 13
#define SD_MISO 2
#define SD_MOSI 15
#define SD_SCLK 14

// //GPIO FUNCTIONS
// #define INPUT               0x01
// #define OUTPUT              0x03
// #define PULLUP              0x04
// #define INPUT_PULLUP        0x05

bool sdcard_begin() {
  if (!sdhander) {
    sdhander = new SPIClass(HSPI);
    pinMode(SD_MISO, INPUT_PULLUP);
    sdhander->begin(SD_SCLK, SD_MISO, SD_MOSI, SD_CS);
  }
  if (!SD.begin(SD_CS, *sdhander)) {
    Serial.println("SD Card Mount Failed");
    return false;
  }
  return true;
}

void print_text(int x, int y, String txt, const uint8_t * font, uint16_t color) {
 
  ttgo->tft->loadFont(font); //ubuntu_regular_18
  ttgo->tft->setTextColor(color);
  ttgo->tft->setCursor(x, y);
  ttgo->tft->print(txt);

}

void setup() {
  Serial.begin(115200);

  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  ttgo->tft->fillScreen(TFT_WHITE);

  //SD karta
  if (sdcard_begin()) {
    Serial.println("sd ok");
  } else {
    Serial.println("sd failed");
    while (1)
      ;  // nekonecna smycka
  }


  uint64_t cardSize = SD.cardSize() / (1024 * 1024);
  Serial.printf("SD Card Size: %lluMB\n", cardSize);

  Serial.println("setup hotov");
  delay(3000);
}

void loop() {

  //ttgo->tft->fillScreen(TFT_WHITE);

  drawPic(10, 10, 150, 150, "/test.raw");

  print_text(365, 10, "Aug 28, 18:58", ubuntu_regular_18, TFT_BLACK);



  delay(1000);
}