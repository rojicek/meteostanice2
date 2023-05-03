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
// processing - vybrat font a ulozit jako wlv (nepouzit smooth)
//https://wiki.seeedstudio.com/Wio-Terminal-LCD-Anti-aliased-Fonts/
//vlw prevest na byty: https://tomeko.net/online_tools/file_to_hex.php?lang=en

#include "config.h"


#include <SPI.h>
#include <SD.h>
//#include <FS.h>

#include "ubuntu_fonts.h"

//#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

void drawBox(int x, int y, int w, int h, uint16_t color) {

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      int ix = 2 * (w * (i - x) + (j - y));  //counter

      drawPixel(i, j, color);
    }
  }
}

void drawPic(int x, int y, int h, int w, String pic) {

  Serial.println("draw 1");

  File picFile = SD.open(pic);
  Serial.println("draw 2");

  uint8_t* pbuffer = nullptr;
  pbuffer = (uint8_t*)malloc(w * h * 2);
  Serial.println("draw 3");

  if (picFile) {
    Serial.print("trying..:");
    picFile.read(pbuffer, w * h * 2);
    Serial.println("read!");
  }

  Serial.print("picFile:");
  Serial.println(picFile);
  close(picFile);

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      //int ix = 2 * (w * (i - x) + (j - y));  //counter
      int ix = 2 * (w * (j - x) + (i - y));

      drawPixel(j, i, 256 * pbuffer[ix] + pbuffer[ix + 1]);
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

void print_text(int x, int y, String txt, const uint8_t* font, uint16_t color) {

  ttgo->tft->loadFont(font);
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

  drawPic(10, 30, 150, 150, "/test.raw");  //pocasi

  print_text(10, 5, "6:33", ubuntu_regular_23, TFT_BLACK);
  drawBox(55, 5, 25, 25, TFT_SKYBLUE);  //vychod zapad
  //drawPic(10, 30, 150, 150, "/test.raw"); //pocasi
  print_text(100, 5, "21:44", ubuntu_regular_23, TFT_BLACK);

  print_text(320, 5, "Aug 28, 18:58", ubuntu_regular_23, TFT_BLACK);

  print_text(10, 185, "25°C", ubuntu_bold_45, TFT_BLACK);
  print_text(120, 190, "21°C", ubuntu_regular_30, TFT_BLACK);

  //drawBox(50, 240, 30, 30, TFT_SKYBLUE); //temp trend
  drawPic(50, 240, 30, 30, "/trend_small_up.raw");  //temp trend
  print_text(90, 240, "-2°C", ubuntu_regular_30, TFT_BLACK);

  //drawBox(10, 275, 30, 30, TFT_SKYBLUE); //kompas
  drawPic(10, 275, 30, 30, "/compass-nw.raw");  //kompas - blbe!

  print_text(50, 275, "5m/s", ubuntu_regular_30, TFT_BLACK);
  //drawBox(120, 275, 30, 30, TFT_SKYBLUE); //w drop
  drawPic(120, 275, 30, 30, "/w-drop.raw");  //w drop
  print_text(160, 275, "2mm", ubuntu_regular_30, TFT_BLACK);

  print_text(250, 40, "NOW", ubuntu_bold_35, TFT_BLACK);
  print_text(360, 40, "THU", ubuntu_bold_35, TFT_BLACK);

  //cycling info
  //drawBox(260, 90, 75, 75, TFT_GREEN);
  drawPic(260, 90, 75, 75, "/cycle_2.raw");
  //drawBox(360, 90, 75, 75, TFT_GREEN);
  drawPic(360, 90, 75, 75, "/cycle_2.raw");

  //vzduch
  //drawBox(260, 190, 50, 50, TFT_DARKGREEN);
  drawPic(260, 190, 50, 50, "/air1A.raw");  //vzduch

  print_text(315, 190, "NOW, +1h", ubuntu_regular_30, TFT_BLACK);
  print_text(315, 220, "+4h, +5h", ubuntu_regular_30, TFT_BLACK);

  //grafika HDO
  drawBox(260, 260, 60, 40, TFT_MAROON);
  drawBox(320, 260, 140, 40, TFT_DARKGREEN);

  print_text(320, 268, "18:00", ubuntu_regular_23, TFT_WHITE);




  delay(1000);
}