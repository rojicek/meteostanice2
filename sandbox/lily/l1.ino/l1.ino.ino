
#include <TFT_eSPI.h> // Hardware-specific library
#include <SPI.h>



#define RED       0xF800

TFT_eSPI tft = TFT_eSPI();                   // Invoke custom library with default width and height 

void setup() {
  Serial.begin(115200);
  while (!Serial) continue;


  tft.reset();
  tft.begin(0x9481);
  tft.setRotation(1);
  tft.fillScreen(RED);

  
  Serial.println("inicializace hotova");
  delay(1000);

}

void loop() {
  Serial.println("ping pong 4");
  //tft.fillScreen(RED);
  delay(1000);

}
