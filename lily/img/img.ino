//https://www.mischianti.org/images-to-byte-array-online-converter-cpp-arduino/
//https://cloudconvert.com/svg-to-bmp

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

  ttgo->tft->fillScreen(TFT_WHITE);

  delay(2000);

  // ttgo->tft->fillScreen(TFT_BLUE);

  // delay(2000);

  //picFile = SD.open("/test_img.bin", FILE_READ);
  picFile = SD.open("/test.bmp", FILE_READ);
  // String obrazek = "";
  //int count = 0;

  if (picFile) {
    Serial.println("budu cist z karty");

    picFile.seek(0x12);  // width in pixel = 16
    int width = picFile.read();
    picFile.seek(0x16);  // height in pixel = 208
    int height = picFile.read();
    Serial.println(width);
    Serial.println(height);
    Serial.println("-----");

    picFile.seek(0x36);  //skip bitmap header

    for (int i = 0; i < height; i++) {
      for (int j = 0; j < width; j++) {
        int rgb = picFile.read();
        //int c2 = 256 * c + c;
        //String stringOne = String(c2, HEX);
        //Serial.println(stringOne);
        //        Serial.println(_HEX(0));
        //int chex = 0xF81D;
        //int chex = 0x4000;
        //drawPixel(i, j, COLOR565(c, c, c));
        //int r = (rgb) & 0b111000000;
        //int g = (rgb << 3) & 0b111000000;
        //int b = (rgb << 6) & 0b11000000;

        int r = (rgb >> 5) * 32;
        int g = ((rgb >> 2) << 3) * 32;
        int b = (rgb << 6) * 64;

        drawPixel(i, j, COLOR565(r, g, b));
      }
    }

    picFile.close();

    //char buf[30000];
    //char ch;
    //byte sEventBuffer[2];

    //int avail_len = picFile.available();
    //ch = picFile.read();     // read the first character
    //int read_len = picFile.readBytes(buf, 10);  // read the remaining to buffer

    //Serial.print(ch);

    //int avail_len = picFile.available();
    //int read_len = picFile.readBytes((char *)buf, 10);
    //int read_len = picFile.readBytes((char *)sEventBuffer,2);

    //Serial.print("Length: ");
    //Serial.println(avail_len);

    //Serial.println(read_len);
    //Serial.println("Content:");
    //Serial.println(buf);
    //Serial.println("-------------------------");
    /*
    int i = 0;
    while (picFile.available()) {
      int x = i / 150;
      int y = i % 150;
      //Serial.print(x);
      //Serial.print("~");
      //Serial.println(y);
      i = i + 1; //nactl jsem 2 byty

      //drawPixel(x, y, _HEX(3969));
      drawPixel(x, y, 0xF81D);
      
      char a = picFile.read();
      //byte b = picFile.read();

      //drawPixel(x, y, 256*a+b);

      //Serial.write((int)a);
      //Serial.write((int)b);
      Serial.println(a, HEX);

      //Serial.write("~");
    }
*/
    picFile.close();

  } else {
    Serial.println("soubor nenalezen ...?");
  }
  delay(2000);
}