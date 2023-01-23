# ruzny api pocasi
import requests
import json
import time

# presne Badenka
lat = 50.0973722
lon = 14.4074581
API_key = '5cb11160e6649574aca2ee031feda8d4'
T0 = 273.15

url1_geo = F'http://api.openweathermap.org/geo/1.0/direct?q=Prague,CZ,CZ&appid={API_key}'
url1_weather = f'https://api.openweathermap.org/data/3.0/onecall?lat={lat}&lon={lon}&appid={API_key}&units=metric'
j1 = requests.get(url1_weather)
w1 = json.loads(j1.text)

t1 = w1['current']['temp']
t1_feel = w1['current']['feels_like']
i1 = w1['current']['weather'][0]['main']
i1desc = w1['current']['weather'][0]['description']
icon1 = w1['current']['weather'][0]['icon']
wspeed1 = w1['current']['wind_speed']
wdir1 = w1['current']['wind_deg']
atime1 = w1['current']['dt']
sunup1 = w1['current']['sunrise']
sundown1 = w1['current']['sunset']

print('OpenWeather')
print(f'Aktualni cas {time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(atime1))}')
print(f'Vychod {time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(sunup1))}')
print(f'Zapad {time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(sundown1))}')
print(f'teplota {round(t1,1)}C ({round(t1_feel,1)}C)')
print(f'vitr {round(wspeed1,1)} at {wdir1}')
print(f'{i1} - {i1desc}')
print(f'{icon1}')



if True:
    # https://www.weatherapi.com/
    API2_key = '14f721672a2b4ee7af7141825231901'
    url2_weather = 'http://api.weatherapi.com/v1/current.json?key=14f721672a2b4ee7af7141825231901&q=Prague&aqi=no'


    j2 = requests.get(url2_weather)
    w2 = json.loads(j2.text)
    t2 = w2['current']['temp_c']
    t2_feel = w2['current']['feelslike_c']
    wspeed2 = w2['current']['wind_kph'] / 3.6 #m/s
    wdir2 = w2['current']['wind_degree']
    i2 = w2['current']['condition']['text']

    print(f'---------------')
    print(f'WeatherAPI')
    print(f'teplota {round(t2,1)}C  ({round(t2_feel,1)}C)')
    print(f'vitr {round(wspeed2,1)} at {wdir2}')
    print(f'{i2}')




