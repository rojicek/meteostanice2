#include <WiFi.h>
#include <ESP32Time.h>

#include "config.h"
#include "time.h"



TTGOClass *ttgo;
const char *wifi_ap = "R_host";
const char *wifi_pd = "badenka5";
const char *ntpServer = "pool.ntp.org";
const long gmtOffset_sec = 3600;
const int daylightOffset_sec = 3600;

struct tm aktualni_cas;
struct tm zobrazeny_cas;
ESP32Time rtc(3600);

void setup() {
  Serial.begin(115200);

  //nastav obrazovku
  ttgo = TTGOClass::getWatch();
  ttgo->begin();
  ttgo->openBL();

  // nastav presny cas
  wifi_connect();
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  Serial.print(gmtime());
  rtc.setTime(gmtime());
  //getTime();
  wifi_disconnect();
  

  //Serial.print(aktualni_cas);
  //Serial.print(&aktualni_cas);
  //Serial.println (asctime(&aktualni_cas));
  Serial.print("inicializovano");
}

// smycky
// 1s ... cas
// 5min ... pocasi
// 1 den ... configTime (abych mel presny cas)

void loop() {
 
  //get actual time
 // if (!getLocalTime(&aktualni_cas)) {
 //   Serial.println("nejde ziskat aktualni cas");
 // }
 

  //if (aktualni_cas > zobrazeny_cas)
  {
    //prepisu cas
     
      Serial.println(rtc.getTimeDate()); 


      //Serial.println (asctime(&aktualni_cas));
      //Serial.println (asctime(&local));
      
  }

  delay(1000);
} //loop

///////////////////////
void wifi_connect()
{ 
    WiFi.begin(wifi_ap, wifi_pd);
    while (WiFi.status() != WL_CONNECTED) {
        delay(200);
        Serial.print(".");
    }
}
void wifi_disconnect()
{
  WiFi.disconnect();
  //asi nemusim cekat na odpojeni
  /* 
    while (WiFi.status() != WL_DISCONNECTED) {
        delay(100);
        Serial.println("+");
    }
  */
}
