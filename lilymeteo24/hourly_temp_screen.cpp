#include "hourly_temp_screen.h"
#include "config.h"
#include "show_elements.h"
#include "ubuntu_fonts.h"
#include "InterpolationLib.h"
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "wifi.h"

#include <ESP32Time.h>

#define DAY_SIZE 24
#define myNAN -9999
#define SCREEN_WIDTH 480
#define SCREEN_HEIGHT 320
#define X_OFFSET 20
#define Y_OFFSET_TOP 20
#define Y_OFFSET_BOTTOM 30
#define PROGRESSBAR_HEIGHT 2
#define SCREEN_DELAY 30  // sec - za jak dlouho obrazovka zmizi

extern TTGOClass* ttgo;

void show_hourly_temp_screen() {

  Serial.println("showing hourly temperature screen");

  // aby bylo videt, ze neco delam
  ttgo->tft->fillScreen(TFT_WHITE);


  //pocitadlo ktere bude mizet
  drawBox(0, SCREEN_HEIGHT - PROGRESSBAR_HEIGHT, SCREEN_WIDTH, PROGRESSBAR_HEIGHT, TFT_RED);

  // nakresli osy

  // spodni osa
  ttgo->tft->drawLine(X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, SCREEN_WIDTH - X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  //osa teploty
  ttgo->tft->drawLine(X_OFFSET, Y_OFFSET_TOP, X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  //osa deste
  ttgo->tft->drawLine(SCREEN_WIDTH - X_OFFSET, Y_OFFSET_TOP, SCREEN_WIDTH - X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  //time tick
  int ticks_count = 8;
  int tick_step = (SCREEN_WIDTH - 2 * X_OFFSET) / ticks_count;
  for (int x_tick = X_OFFSET; x_tick < SCREEN_WIDTH - X_OFFSET; x_tick += tick_step) {
    //zatim testy
    show_text(-100, x_tick, SCREEN_HEIGHT - Y_OFFSET_BOTTOM + 2, ubuntu_regular_23, "", "15h");
    //show_text(int past_x, int x, int y, const unsigned char* font, String shown, String actual)
  }



  String x_casy[DAY_SIZE];
  double x_raw[DAY_SIZE];
  double y_teplota[DAY_SIZE];

  double max_temp = -999.;
  double min_temp = 999.;

  if (wifi_connect() == 0) {

    JsonDocument hourly_doc;
    HTTPClient http;
    String url = "https://www.rojicek.cz/meteo/get-daily-ascii.php";


    http.begin(url.c_str());

    int httpResponseCode = http.GET();

    if (httpResponseCode == 200) {
      Serial.println("hourly data query ok");
      String payload = http.getString();

      DeserializationError error = deserializeJson(hourly_doc, payload);

      if (error) {
        Serial.print(F("deserializeJson() failed: "));
        Serial.println(error.f_str());
        Serial.println("zpatky k hlavni obrazovce ");
        wifi_disconnect();
        ttgo->tft->fillScreen(TFT_WHITE);  //uklid co jsi namaloval
        return;
      }



      JsonArray daily_arr = hourly_doc["rows"].as<JsonArray>();

      int ix = 0;
      for (JsonVariant value : daily_arr) {
        //Serial.println("---------");
        String cas_plot = value["c"][0]["v"].as<String>();
        double teplota_plot = value["c"][6]["v"].as<double>();
        double snih_plot = value["c"][3]["v"].as<double>();
        double dest_plot = value["c"][1]["v"].as<double>();

        max_temp = max(max_temp, teplota_plot);
        min_temp = min(min_temp, teplota_plot);

        x_casy[ix] = cas_plot;  //jen label
        x_raw[ix] = ix;

        y_teplota[ix] = teplota_plot;

        ix++;
      }

    }  // konec 200 http

    wifi_disconnect();
  }

  //mam nactena data, ted je nakreslit






  // todo:
  // pripocitat nejaky prostor k min a max (podle webu, tak to vypada ok)
  // teploty chci prepocitat

  float temp_band = max(0.1 * (max_temp - min_temp), 1.4);
  min_temp = min_temp - temp_band;
  max_temp = max_temp + temp_band;

  // kresleni plotu
  float x_plot_prev = myNAN;
  float y_plot_prev = myNAN;

  //smycka pres 24 aka DAY_SIZE, ale kvuli interpolovane care delam mensi kroky
  for (double hour_of_day = 0; hour_of_day <= DAY_SIZE; hour_of_day += 0.1) {


    double y_plot_interpolate = Interpolation::CatmullSpline((double*)x_raw, (double*)y_teplota, DAY_SIZE, (double)hour_of_day);
    double x_plot = (double)(SCREEN_WIDTH - 2 * X_OFFSET) * hour_of_day / (double)DAY_SIZE + (double)X_OFFSET;


    // tohle by bylo bez interpolace, ale iix by muselo byt jen 0-23 jako index
    //float y_plot = 310 - 300. * (y_teplota[iix] - min_temp) / (max_temp - min_temp);

    //s intepolaci
    double y_plot = 310 - 300. * (y_plot_interpolate - min_temp) / (max_temp - min_temp);

    if (x_plot_prev != myNAN) {
      //nekresli prvni bod, neni s cim spojit
      plotLineWidth(int(x_plot_prev), int(y_plot_prev), int(x_plot), int(y_plot), 4, TFT_RED);
    }

    x_plot_prev = x_plot;
    y_plot_prev = y_plot;
  }
  Serial.println("***************");


  /// cekam na konec
  delay(100);  // abych stihl dat prst pryc
  ESP32Time board_time(0);
  unsigned long current_epoch = board_time.getEpoch();
  unsigned long expected_end_epoch = current_epoch + SCREEN_DELAY;  // za 30s

  while ((expected_end_epoch > current_epoch) && (ttgo->touched() == 0)) {

    double zbyva_procent = (double)(expected_end_epoch - current_epoch) / (double)SCREEN_DELAY;
    int zbyva_pixelu = (int)(zbyva_procent * (double)SCREEN_WIDTH);

    drawBox(zbyva_pixelu, SCREEN_HEIGHT - PROGRESSBAR_HEIGHT, SCREEN_WIDTH - zbyva_pixelu, PROGRESSBAR_HEIGHT, TFT_WHITE);

    delay(100);
    current_epoch = board_time.getEpoch();
  }

  // uklid
  ttgo->tft->fillScreen(TFT_WHITE);

  return;
}