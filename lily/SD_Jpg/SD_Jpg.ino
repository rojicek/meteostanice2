// Example for library:
// https://github.com/Bodmer/TJpg_Decoder

// This example if for an ESP8266 or ESP32, it renders a Jpeg file
// that is stored in a SD card file. The test image is in the sketch
// "data" folder (press Ctrl+K to see it). You must save the image
// to the SD card using you PC.

// Include the jpeg decoder library
//#include <TJpg_Decoder.h>

#include "config.h"
#include "i.h"

#define BIRDW 132  // bird width
#define BIRDH 142

// Include SD
#define FS_NO_GLOBALS
#include <FS.h>
#ifdef ESP32
#include "SPIFFS.h"  // ESP32 only
#endif

#define mySD_CS 4
#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)

TTGOClass* ttgo;


// Include the TFT library https://github.com/Bodmer/TFT_eSPI
/*
#include "SPI.h"
#include <TFT_eSPI.h>              // Hardware-specific library
TFT_eSPI tft = TFT_eSPI();         // Invoke custom library
*/



// This next function will be called during decoding of the jpeg file to
// render each block to the TFT.  If you use a different TFT library
// you will need to adapt this function to suit.
/*
bool tft_output(int16_t x, int16_t y, uint16_t w, uint16_t h, uint16_t* bitmap) {
  // Stop further decoding as image is running off bottom of screen
  if (y >= ttgo->tft->height()) return 0;

  // This function will clip the image block rendering automatically at the TFT boundaries
  ttgo->tft->pushImage(x, y, w, h, bitmap);

  // This might work instead if you adapt the sketch to use the Adafruit_GFX library
  // tft.drawRGBBitmap(x, y, bitmap, w, h);

  // Return 1 to decode next block
  return 1;
}
*/

void setup() {
  Serial.begin(115200);
  Serial.println("\n\n Testing TJpg_Decoder library");

  // Initialise SD before TFT
  /*
  if (!SD.begin(4)) {
    Serial.println(F("SD.begin failed!"));
    while (1)
      delay(10);
  }
  Serial.println("\r\nSD Initialisation done.");
*/
  //uint16_t w = 0, h = 0;
  //TJpgDec.getSdJpgSize(&w, &h, "/panda.jpg");
  //Serial.println("get SD img");

  //TJpgDec.getJpgSize(&w, &h, ii, sizeof(ii));


  //Serial.println(w);  //funguje pro sd i h
  //Serial.println(h);  //funguje pro sd i h
  //Serial.println(sizeof(ii));


  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();
  //ttgo->tft->setRotation(1);

  ttgo->tft->fillScreen(TFT_WHITE);

  static short tmpx, tmpy;
  unsigned char px;
  long bird_x = 0;
  long bird_y = 0;

  long bird_ox = 0;
  long bird_oy = 0;


  tmpx = BIRDW - 1;
  do {
    px = bird_x + tmpx + BIRDW;
    // clear bird at previous position stored in old_y
    // we can't just erase the pixels before and after current position
    // because of the non-linear bird movement (it would leave 'dirty' pixels)
    tmpy = BIRDH - 1;
    do {
      drawPixel(px, bird_oy + tmpy, TFT_WHITE);
    } while (tmpy--);
    // draw bird sprite at new position
    tmpy = BIRDH - 1;
    do {
      drawPixel(px, bird_y + tmpy, logo[tmpx + (tmpy * BIRDW)]);
    } while (tmpy--);
  } while (tmpx--);
  // save position to erase bird on next draw


  /*
  // The decoder must be given the exact name of the rendering function above
  TJpgDec.setCallback(tft_output);
  TJpgDec.setJpgScale(1);
  TJpgDec.setSwapBytes(true);

  //TJpgDec.drawJpg(0, 0, ii, sizeof(ii));

  ttgo->tft->pushImage(0, 0, 8, 8, ii);
  //



  //TJpgDec.drawSdJpg(0, 0, "/panda.jpg");
*/

  /*
  // Initialise the TFT
  tft.begin();
  tft.setTextColor(0xFFFF, 0x0000);
  tft.fillScreen(TFT_RED);
  tft.setSwapBytes(true); // We need to swap the colour bytes (endianess)
*/
  // The jpeg image can be scaled by a factor of 1, 2, 4, or 8

  Serial.println("konec init");
}

void loop() {

  return;
  //ttgo->tft->fillScreen(TFT_GREEN);

  // Time recorded for test purposes
  uint32_t t = millis();

  // Get the width and height in pixels of the jpeg if you wish
  //uint16_t w = 0, h = 0;
  //TJpgDec.getSdJpgSize(&w, &h, "/panda.jpg");
  //panda.jpg
  delay(2000);

  t = millis() - t;
  Serial.print(t);
  Serial.println(" ms");

  return;
  /*
  
  Serial.print("Width = "); Serial.print(w); Serial.print(", height = "); Serial.println(h);

  // Draw the image, top left at 0,0
  TJpgDec.drawSdJpg(0, 0, "/panda.jpg");

  // How much time did rendering take
 
*/
  // Wait before drawing again
  delay(2000);
}
