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
#define X_OFFSET 10
#define Y_OFFSET_TOP 20
#define Y_OFFSET_BOTTOM 30
#define PROGRESSBAR_HEIGHT 2
#define SCREEN_DELAY 30  // sec - za jak dlouho obrazovka zmizi
#define TICKS_COUNT 8    //kolik x labelu mam zobrazit, vynechavam ty mezi

#define X_TIMETICK_LEFT_OFFSET 3
#define X_RIGHT_LABELS 400


#define X_TICK_GAP (SCREEN_WIDTH - 2 * X_OFFSET) / DAY_SIZE

// definuje obvyklou sirku pro label teploty resp srazky
// polohy pocitam podle aktualnich hodnod
#define X_WIDTH_COLLIDER_4TEMP 20
#define X_WIDTH_COLLIDER_4PERC 50

#define SRAZKY_BOX_WIDTH 8

extern TTGOClass* ttgo;

int x_axis_convert(double x) {
  // tohle je korektne ... ale krajni srazkove boxy jsou mimo osy
  double x_plot = (double)(SCREEN_WIDTH - 2 * X_OFFSET) * x / (double)(DAY_SIZE - 1) + (double)X_OFFSET;

  // pokusy
  //int adjusted_x_offset = X_OFFSET + 2;
  //double x_plot = (double)(SCREEN_WIDTH - 2 * adjusted_x_offset) * x / (double)(DAY_SIZE - 1) + (double)adjusted_x_offset;

  return (int)x_plot;
}

int y_axis_convert(double y, double min_temp, double max_temp) {
  // double y_plot = 310 - 300. * (y - min_temp) / (max_temp - min_temp);
  double y_plot = (double)(SCREEN_HEIGHT - Y_OFFSET_BOTTOM) - (double)(SCREEN_HEIGHT - Y_OFFSET_TOP - Y_OFFSET_BOTTOM) * (y - min_temp) / (max_temp - min_temp);
  return (int)y_plot;
}

void show_hourly_temp_screen() {

  Serial.println("showing hourly temperature screen");

  // aby bylo videt, ze neco delam
  ttgo->tft->fillScreen(TFT_WHITE);


  //pocitadlo ktere bude mizet
  drawBox(0, SCREEN_HEIGHT - PROGRESSBAR_HEIGHT, SCREEN_WIDTH, PROGRESSBAR_HEIGHT, TFT_DARKGREEN, 0);

  // nakresli osy

  // spodni osa
  ttgo->tft->drawLine(X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, SCREEN_WIDTH - X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  //osa teploty
  ttgo->tft->drawLine(X_OFFSET, Y_OFFSET_TOP, X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  //osa deste
  ttgo->tft->drawLine(SCREEN_WIDTH - X_OFFSET, Y_OFFSET_TOP, SCREEN_WIDTH - X_OFFSET, SCREEN_HEIGHT - Y_OFFSET_BOTTOM, TFT_BLACK);

  // test teplot labels
  //show_text(-100, X_OFFSET + 3, 100, ubuntu_regular_23, "", "22°C");


  String x_casy[DAY_SIZE];
  double x_raw[DAY_SIZE];
  double y_teplota[DAY_SIZE];
  double y_dest[DAY_SIZE];
  double y_snih[DAY_SIZE];

  double max_temp = -999.;
  double min_temp = 999.;

  // double prvni_teplota = 999;
  // double posledni_teplota = 999;
  // double predposledni_teplota = 999;


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
        y_dest[ix] = dest_plot;
        y_snih[ix] = snih_plot;

        /*
        if (ix == 0) {
          //prvni teplota pro graf
          prvni_teplota = teplota_plot;
        }

        // bude fungovat jen pro vice nez 2 hodnoty ve smycce, ale to je bezpecne
        predposledni_teplota = posledni_teplota;
        // porad prepisuji az mi tam zustane posledni
        posledni_teplota = teplota_plot;
*/

        ix++;
      }

    }  // konec 200 http

    wifi_disconnect();
  }

  //nahore necham trochu vice mista kvuli ikonam
  float temp_band_hi = max(0.1 * (max_temp - min_temp), 1.2);
  float temp_band_low = max(0.1 * (max_temp - min_temp), 1.0);
  min_temp = min_temp - temp_band_low;
  max_temp = max_temp + temp_band_hi;


  //mam nactena data, ted je nakreslit

  //time tick
  for (int iix = 0; iix < 24; iix += DAY_SIZE / TICKS_COUNT) {
    // vejde se kazdy 3 tick
    int x_tick = X_OFFSET + iix * X_TICK_GAP;

    // -100: nechci nic mazat
    show_text(-100, x_tick, SCREEN_HEIGHT - Y_OFFSET_BOTTOM + 2, ubuntu_regular_23, "", x_casy[iix]);
  }

  int temp_low = ceil(min_temp);
  int temp_high = floor(max_temp);
  int temp_step = 1;

  //srazky maji pevne meritko
  int srazky_high = 10;
  int srazky_low = 0;
  int srazky_step = 2;

  // pokud je prilis velky rozsah teplot, tak kresli kazdy druhy

  if (temp_high - temp_low >= 10)
    temp_step = 2;

  //int y_prvni_teplota = y_axis_convert(prvni_teplota, min_temp, max_temp);
  //int y_posledni_teplota = y_axis_convert(posledni_teplota, min_temp, max_temp);
  //int y_predposledni_teplota = y_axis_convert(predposledni_teplota, min_temp, max_temp);

  // kolider - default neni zadna kolize
  int y_collider_left_low_px = SCREEN_HEIGHT;
  int y_collider_left_high_px = 0;
  int y_collider_right_low_px = SCREEN_HEIGHT;
  int y_collider_right_high_px = 0;
  int y_collider_left_srazky = 0;


  // kresleni plotu
  int x_plot_prev = myNAN;
  int y_plot_prev = myNAN;

  //nejdrive nakresli horizontalni osy - muzou se prekreslit cimkoliv
  for (int temp_tick = temp_low; temp_tick <= temp_high; temp_tick += temp_step) {
    int y_temp = y_axis_convert(temp_tick, min_temp, max_temp);

    // todo: neni uplne idealni, nemel bych se divat na min/max temp, ale na realne pouzite labely
    int extra_x_offet_temp_labels = 0;
    if ((abs(min_temp) >= 10) || (abs(max_temp) >= 10))
      extra_x_offet_temp_labels = 10;
    ttgo->tft->drawLine(X_OFFSET + 30 + extra_x_offet_temp_labels, y_temp, SCREEN_WIDTH - X_OFFSET, y_temp, TFT_SILVER);
  }

  // smycka pres 24 aka DAY_SIZE, ale kvuli interpolovane care delam mensi kroky
  // posledni krok nedelam ve smycce, ale zaridi ho interpolace
  for (double hour_of_day = 0; hour_of_day < DAY_SIZE - 1; hour_of_day += 0.1) {

    double y_interpolate = Interpolation::CatmullSpline((double*)x_raw, (double*)y_teplota, DAY_SIZE, (double)hour_of_day);
    int x_plot = x_axis_convert(hour_of_day);

    // s intepolaci
    // bez interpolace pouzij y_teplota[iix], ale musel bych si prepsat smycku pro index
    int y_plot = y_axis_convert(y_interpolate, min_temp, max_temp);

    // zapis si levy kolider
    if ((x_plot >= X_OFFSET + X_TIMETICK_LEFT_OFFSET) && (x_plot <= X_OFFSET + X_TIMETICK_LEFT_OFFSET + X_WIDTH_COLLIDER_4TEMP)) {
      //posledni cislo = sirka temp labelu, staci priblizne
      y_collider_left_low_px = min(y_collider_left_low_px, y_plot);
      y_collider_left_high_px = max(y_collider_left_high_px, y_plot);
    }

    // zapis si pravy kolider
    if ((x_plot >= X_RIGHT_LABELS) && (x_plot <= X_RIGHT_LABELS + X_WIDTH_COLLIDER_4PERC)) {
      //posledni cislo  = sirka temp labelu, staci priblizne
      y_collider_right_low_px = min(y_collider_right_low_px, y_plot);
      y_collider_right_high_px = max(y_collider_right_high_px, y_plot);
    }


    if (x_plot_prev != myNAN) {
      //nekresli prvni bod, neni s cim spojit
      plotLineWidth(x_plot_prev, y_plot_prev, x_plot, y_plot, 3, TFT_NAVY);
    }

    // zapamatuj si predchozi bod
    x_plot_prev = x_plot;
    y_plot_prev = y_plot;
  }  //konec plotu teploty

  // kresleni srazek (mozna to pujde v 1 smycce, ale tohle je bez interpolace: todo)
  // nakreslim jen 23h a cele to bude trochu posunute, ale to je asi jedno
  for (int ix_hour_of_day = 0; ix_hour_of_day < DAY_SIZE - 1; ix_hour_of_day++) {

    int x_srazky_plot = x_axis_convert((double)ix_hour_of_day);
    int y_srazky_plot = SCREEN_HEIGHT - Y_OFFSET_BOTTOM - y_axis_convert(y_dest[ix_hour_of_day], srazky_low, srazky_high);
    int y_snih_plot = SCREEN_HEIGHT - Y_OFFSET_BOTTOM - y_axis_convert(y_snih[ix_hour_of_day], srazky_low, srazky_high);

    //Serial.println(y_srazky_plot);

    if (ix_hour_of_day == 0)
      y_collider_left_srazky = y_srazky_plot + y_snih_plot;

    //dole je dest
    drawBox(x_srazky_plot + 5, SCREEN_HEIGHT - Y_OFFSET_BOTTOM - y_srazky_plot, 12, y_srazky_plot, TFT_SKYBLUE, 0);

    //nahore snih
    drawBox(x_srazky_plot + 5, SCREEN_HEIGHT - Y_OFFSET_BOTTOM - y_snih_plot - y_srazky_plot, 12, y_snih_plot, TFT_WHITE, 1);
  }

  //////

  // kresleni popisu osy y (teploty)
  for (int temp_tick = temp_low; temp_tick <= temp_high; temp_tick += temp_step) {

    int y_temp = y_axis_convert(temp_tick, min_temp, max_temp);

    // jestli budu kresli nebo jestli mi to nekde koliduji
    int kresli = 1;

    // koliduje s carou, 15 je asi velikost pisma
    if ((y_temp + 15 >= y_collider_left_low_px) && (y_temp <= y_collider_left_high_px)) {
      kresli = 0;
    }

    //kolider teploty se spodni osou
    if (y_temp >= SCREEN_HEIGHT - Y_OFFSET_BOTTOM - 10) {
      kresli = 0;
    }

    //kolize teploty a srazky
    if (y_temp > SCREEN_HEIGHT - Y_OFFSET_BOTTOM - y_collider_left_srazky - 5)
    {
      kresli = 0;
    }

    //Serial.print(temp_tick);
    //Serial.print(" : ");
    //Serial.println(y_temp);



    //stary kolider
    // if ((y_temp < SCREEN_HEIGHT - Y_OFFSET_BOTTOM - 10) && (abs(y_temp - y_prvni_teplota) > 8))

    //novy kolider
    if (kresli == 1) {
      //spodni cislo vynecham, kdyz je moc blizko osy (10px od osy)
      show_text(-100, X_OFFSET + X_TIMETICK_LEFT_OFFSET, y_temp - 10, ubuntu_light_18, "", String(temp_tick) + "°C");
    }
  }

  // srazky tick (cary nekreslim kvuli prehlednosti)


  for (int srazky_tick = srazky_step; srazky_tick < srazky_high; srazky_tick += srazky_step) {

    int y_srazky = y_axis_convert(srazky_tick, srazky_low, srazky_high);
    int kresli = 1;

    // + 6 je vyska fontu (nevim proc 6, proste se mi to tak libi)
    if ((y_srazky + 6 >= y_collider_right_low_px) && (y_srazky <= y_collider_right_high_px)) {
      kresli = 0;
    }

    if (kresli == 1) {
      // nepisu popisek srazek, kdyz koliduje s teplotou
      // porovnavam pixely, tak srazky a teplota jsou ok
      show_text(-100, X_RIGHT_LABELS, y_srazky - 5, ubuntu_light_18, "", String(srazky_tick) + " mm/h");

      // cary srazek asi nechci, ale vypadaly by takhle
      // ttgo->tft->drawLine(X_OFFSET + 50, y_srazky, SCREEN_WIDTH - X_OFFSET, y_srazky, TFT_RED);
    }
  }



  // konec kresleni


  /// cekam na konec
  delay(100);  // abych stihl dat prst pryc
  ESP32Time board_time(0);
  unsigned long current_epoch = board_time.getEpoch();
  unsigned long expected_end_epoch = current_epoch + SCREEN_DELAY;  // za 30s

  while ((expected_end_epoch > current_epoch) && (ttgo->touched() == 0)) {

    double zbyva_procent = (double)(expected_end_epoch - current_epoch) / (double)SCREEN_DELAY;
    int zbyva_pixelu = (int)(zbyva_procent * (double)SCREEN_WIDTH);

    drawBox(zbyva_pixelu, SCREEN_HEIGHT - PROGRESSBAR_HEIGHT, SCREEN_WIDTH - zbyva_pixelu, PROGRESSBAR_HEIGHT, TFT_WHITE, 0);

    delay(100);
    current_epoch = board_time.getEpoch();
  }

  // uklid
  ttgo->tft->fillScreen(TFT_WHITE);

  return;
}