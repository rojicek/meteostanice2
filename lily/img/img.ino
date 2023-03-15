//https://www.mischianti.org/images-to-byte-array-online-converter-cpp-arduino/
//https://cloudconvert.com/svg-to-bmp


//http://www.rinkydinkelectronics.com/t_imageconverter565.php - TOHLE pouzivam

//https://javl.github.io/image2cpp/

#include "config.h"
#include "extra.h"

#include <SPI.h>
#include <SD.h>
//#include <Streaming.h>

//#define BUFFPIXEL 20

File picFile;


#define COLOR565(r, g, b) ((r & 0xF8) << 8) | ((g & 0xFC) << 3) | (b >> 3)





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
  ttgo->tft->fillScreen(TFT_WHITE);

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
  delay(2000);
}