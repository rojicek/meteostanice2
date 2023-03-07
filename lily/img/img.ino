//https://www.mischianti.org/images-to-byte-array-online-converter-cpp-arduino/

#include "config.h"
#include "extra.h"

#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


TTGOClass* ttgo;

/* ok - obdelnik
#define IMGW 200
#define IMGH 100
*/
#define IMGW 150
#define IMGH 150

void setup() {
  Serial.begin(115200);

  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  ttgo->tft->setRotation(3);

  ttgo->tft->fillScreen(TFT_WHITE);


  static short tmpx, tmpy;
  unsigned char px;
  long bird_x = 0;
  long bird_y = 0;

  long bird_ox = 0;
  long bird_oy = 0;

  for (int x = 0; x < IMGW; x++)
    for (int y = 0; y < IMGH; y++) {
      //plot
      drawPixel(x, y, cervena[x+y*IMGW]);
    }
/*
  tmpx = IMGW - 1;
  do {
    px = bird_x + tmpx + IMGW;
    // clear bird at previous position stored in old_y
    // we can't just erase the pixels before and after current position
    // because of the non-linear bird movement (it would leave 'dirty' pixels)
    tmpy = IMGH - 1;
    do {
      drawPixel(px, bird_oy + tmpy, TFT_WHITE);
    } while (tmpy--);
    // draw bird sprite at new position
    tmpy = IMGH - 1;
    do {
      drawPixel(px, bird_y + tmpy, cervena[tmpx + (tmpy * IMGW)]);
    } while (tmpy--);
  } while (tmpx--);
*/
  Serial.println("setup hotov");
}

void loop() {}