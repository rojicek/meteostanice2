#include "show_elements.h"
#include "ubuntu_fonts.h"


#define drawPixel(a, b, c) \
  ttgo->tft->setAddrWindow(a, b, a, b); \
  ttgo->tft->pushColor(c)


extern TTGOClass* ttgo;

void show_text(int past_x, int x, int y, const unsigned char* font, String shown, String actual) {
  if (shown != actual) {
    ttgo->tft->loadFont(font);
    ttgo->tft->setTextColor(TFT_WHITE);
    ttgo->tft->setCursor(past_x, y);
    ttgo->tft->print(shown);  //delete old
    ttgo->tft->setTextColor(TFT_BLACK);
    ttgo->tft->setCursor(x, y);
    ttgo->tft->print(actual);  //print new
  }
  return;
}



void drawPic(int x, int y, int dimx, int dimy, String pic) {

  File picFile = SD.open(pic);
  //Serial.print("soubor handler:>");
  //Serial.print(picFile);
  //Serial.println("<<<");


  if (picFile == 1) {

    uint8_t* pbuffer = nullptr;
    pbuffer = (uint8_t*)malloc(dimx * dimy * 2);

    picFile.read(pbuffer, dimx * dimy * 2);

    for (int i = x; i < x + dimx; i++) {
      for (int j = y; j < y + dimy; j++) {

        int ix = 2 * ((i - x) + dimx * (j - y));

        drawPixel(i, j, 256 * pbuffer[ix] + pbuffer[ix + 1]);
      }
    }

    if (pbuffer) free(pbuffer);

    //Serial.print(pic);
    //Serial.println(".. ok!");
  } else {
    Serial.print(pic);
    Serial.println(".. not found");

    for (int i = x; i < x + dimx; i++) {
      for (int j = y; j < y + dimy; j++) {

        int ix = 2 * ((i - x) + dimx * (j - y));

        drawPixel(i, j, TFT_WHITE);
      }
    }
  }

  close(picFile);
}

void drawBox(int x, int y, int w, int h, uint16_t color, int okraj) {

  for (int i = x; i < x + w; i++) {
    for (int j = y; j < y + h; j++) {
      int ix = 2 * (w * (i - x) + (j - y));  //counter

      drawPixel(i, j, color);
    }
  }

  if ((okraj == 1) && ( h > 0)) {
    //nakresli okraj, vzdy cerne
    ttgo->tft->drawLine(x, y, x + w - 1, y, TFT_BLACK);
    ttgo->tft->drawLine(x, y + h, x + w - 1, y + h, TFT_BLACK);
    ttgo->tft->drawLine(x, y, x, y + h, TFT_BLACK);
    ttgo->tft->drawLine(x + w - 1, y, x + w - 1, y + h, TFT_BLACK);
  }
}



void plotLineWidth(int start_x, int start_y, int end_x, int end_y, float thickness, uint16_t color) {

  float d = sqrt((end_x - start_x) * (end_x - start_x) + (end_y - start_y) * (end_y - start_y));
  float y_shift = thickness * (end_x - start_x) / (d * 2.0f);
  float x_shift = -thickness * (end_y - start_y) / (d * 2.0f);



  ttgo->tft->fillTriangle(start_x - x_shift, start_y - y_shift, start_x + x_shift, start_y + y_shift, end_x + x_shift, end_y + y_shift, color);
  ttgo->tft->fillTriangle(end_x + x_shift, end_y + y_shift, end_x - x_shift, end_y - y_shift, start_x - x_shift, start_y - y_shift, color);
}
