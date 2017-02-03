#include <ESP8266HTTPClient.h>
#include <Adafruit_NeoPixel.h>
#include <Servo.h>
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <ESP8266WiFi.h>
#include <WiFiManager.h>
//#include "LedControlMS.h"
#include <Arduino.h>

class SpringyValue
{
  public:
    float x = 0, v = 0, a = 0, // x (value), v (velocity) and a (acceleration) define the state of the system
          o = 0, // o defines the temporary spring offset w.r.t. its resting position
          c = 20.0, k = 1.5, m = 1.0; // c (spring constant), k (damping constant) and m (mass) define the behavior of the system

    // Perturb the system to change the "length" of the spring temporarlily
    void perturb(float offset) {
      this->o = offset;
    }

    // Call "update" every now and then to update the system
    // parameter dt specifies the elapsed time since the last update
    void update(float dt) {
      a = (-c * x - k * v ) / m;
      v += a * dt;
      x += v * dt + o;
      o = 0; // a spring offet only takes one frame
    }
};


#define BUTTON_PIN  D1
#define PIN         D2
#define LED_COUNT    6

#define fadeInDelay  5
#define fadeOutDelay 8

#define requestDelay 2000

#define NBR_MTX 2
LedControl lc = LedControl(13, 14, 0, NBR_MTX);

Adafruit_NeoPixel strip = Adafruit_NeoPixel(LED_COUNT, PIN, NEO_GRB + NEO_KHZ400);
Servo myServo;

int oldTime = 0;
int oscillationTime = 500;
String chipID;
char chipIdArray[5] = {};
String webURL = "http://thingscon16.futuretechnologies.nl";

void setAllPixels(uint8_t r, uint8_t g, uint8_t b, float multiplier);

//RELATED to the message scrolling
String scrollingMessage;

void setup()
{
  configureChipID();
  strip.begin();
  strip.setBrightness(255);
  WiFiManager wifiManager;
  Serial.begin(115200);

  pinMode(BUTTON_PIN, INPUT_PULLUP);
  int counter = 0;
  while (digitalRead(BUTTON_PIN) == LOW)
  {
    counter++;
    delay(10);

    if (counter > 500)
    {
      wifiManager.resetSettings();
      Serial.println("Remove all wifi settings!");
      setAllPixels(255, 0, 0, 1.0);
      fadeBrightness(255, 0, 0, 1.0);
      ESP.reset();
    }
  }
  delay(1000);

  Serial.println();
  Serial.print("Last 2 bytes of chip ID: ");
  Serial.println(chipID);

  String wifiNameConcat = "ConnectiKlaas_" + chipID;
  char wifiName[19] = {};
  wifiNameConcat.toCharArray(wifiName, 19);

  setAllPixels(0, 255, 255, 1.0);
  wifiManager.autoConnect(wifiName);
  fadeBrightness(0, 255, 255, 1.0);
  myServo.attach(D6);

  // WAKES UO THE MAX72XX FROM POWER SAVING MODE
  Serial.begin(9600);
  Serial.println("");
  Serial.println("########## SETUP ##########");
  Serial.println("");
  Serial.println("- waking up MAX72XX from power saving mode");

  //LOOP THROUGH THE LEDS TO SWITCH THEM ON
  for (int i = 0; i < NBR_MTX; i++)
  {
    lc.shutdown(i, false);
    /* Set the brightness to a medium values */
    lc.setIntensity(i, 8);
    /* and clear the display */
    lc.clearDisplay(i);
  }
  Serial.println("- success looping through all the leds to switch them on");

  makeHappy();
  scrollingMessage = "Connecting";
  makeSad();

}

void setAllPixels(uint8_t r, uint8_t g, uint8_t b, float multiplier = 1.0)
{
  for (int iPixel = 0; iPixel < LED_COUNT; iPixel++)
    strip.setPixelColor(iPixel,
                        (byte)((float)r * multiplier),
                        (byte)((float)g * multiplier),
                        (byte)((float)b * multiplier));
  strip.show();
}

//This method starts an oscillation movement in both the LED and servo
void oscillate(float springConstant, float dampConstant, int c)
{
  SpringyValue spring;

  byte red = (c >> 16) & 0xff;
  byte green = (c >> 8) & 0xff;
  byte blue = c & 0xff;

  spring.c = springConstant;
  spring.k = dampConstant / 100;
  spring.perturb(255);

  //Start oscillating
  for (int i = 0; i < oscillationTime; i++)
  {


    spring.update(0.01);
    setAllPixels(red, green, blue, abs(spring.x) / 255.0);
    myServo.write(90 + spring.x / 4);

    //Check for button press
    if (digitalRead(BUTTON_PIN) == LOW)
    {
      //Fade the current color out
      makeSad();
      fadeBrightness(red, green, blue, abs(spring.x) / 255.0);
      return;
    }
    delay(10);
  }
  fadeBrightness(red, green, blue, abs(spring.x) / 255.0);
}

//This method grabs the current RGB values and current brightness and fades the colors to black
void fadeBrightness(uint8_t r, uint8_t g, uint8_t b, float currentBrightness)
{
  for (float j = currentBrightness; j > 0.0; j -= 0.01)
  {
    setAllPixels(r, g, b, j);
    delay(20);
  }
  hideColor();
}

void loop()
{
  //React to sounds
  int soundValue;
  soundValue = analogRead(0);
  if(soundValue >= 100)
  {
    Serial.println("NOISY");
    makeHappy();
    delay(50);
  }
  //Check for button press
  if (digitalRead(BUTTON_PIN) == LOW)
  {
    makeSad();
    sendButtonPress();
    delay(250);
  }

  //Every requestDelay, send a request to the server
  if (millis() > oldTime + requestDelay)
  {
    requestMessage();
    oldTime = millis();
  }
}

void sendButtonPress()
{
  Serial.println("Sending button press to server");
  HTTPClient http;
  http.begin(webURL + "/api.php?t=sqi&d=" + chipID);
  uint16_t httpCode = http.GET();
  http.end();
}

void requestMessage()
{
  makeLoading();

  Serial.println("Sending request to server");
  hideColor();

  HTTPClient http;
  http.begin(webURL + "/api.php?t=gqi&d=" + chipID + "&v=2");
  uint16_t httpCode = http.GET();

  if (httpCode == 200)
  {
    String response;
    response = http.getString();
    //Serial.println(response);

    if (response == "-1")
    {
      Serial.println("There are no messages waiting in the queue");
      makeSad();
    }
    else
    {
      //Get the indexes of some commas, will be used to split strings
      int firstComma = response.indexOf(',');
      int secondComma = response.indexOf(',', firstComma + 1);
      int thirdComma = response.indexOf(',', secondComma + 1);

      //Parse data as strings
      String hexColor = response.substring(0, 7);
      String springConstant = response.substring(firstComma + 1, secondComma);
      String dampConstant = response.substring(secondComma + 1, thirdComma);;
      String message = response.substring(thirdComma + 1, response.length());;

      Serial.println("Message received from server: \n");
      Serial.println("Hex color received: " + hexColor);
      Serial.println("Spring constant received: " + springConstant);
      Serial.println("Damp constant received: " + dampConstant);
      Serial.println("Message received: " + message);

      scrollingMessage = message;


      //Extract the hex color and fade the led strip
      int number = (int) strtol( &response[1], NULL, 16);
      oscillate(springConstant.toFloat(), dampConstant.toFloat(), number);
      makeHappy();
      lc.clearAll();
      scrollMessage(scrollingMessage);
      makeHappy();
      delay(500);

    }
  }
  else
  {
    ESP.reset();
  }

  http.end();
}

void hideColor()
{
  colorWipe(strip.Color(0, 0, 0));
}

void colorWipe(uint32_t c)
{
  for (uint16_t i = 0; i < strip.numPixels(); i++)
  {
    strip.setPixelColor(i, c);
  }
  strip.show();
}

void configureChipID()
{
  uint32_t id = ESP.getChipId();
  byte lower = id & 0xff;
  byte upper = (id >> 8) & 0xff;

  String l = "";
  String u = "";

  if (lower < 10)
  {
    l = "0" + String(lower, HEX);
  }
  else
  {
    l = String(lower, HEX);
  }

  if (upper < 10)
  {
    u = "0" + String(upper, HEX);
  }
  else
  {
    u = String(upper, HEX);
  }

  chipID = "B33F"; //u + l; set back after testing
  chipID.toUpperCase();
  chipID.toCharArray(chipIdArray, 5);
}

void makeHappy()
{
  lc.clearAll();
  lc.setLed(0, 1, 1, true);
  lc.setLed(0, 1, 2, true);
  lc.setLed(0, 1, 5, true);
  lc.setLed(0, 1, 6, true);
  lc.setLed(0, 2, 1, true);
  lc.setLed(0, 2, 6, true);
  lc.setLed(0, 5, 1, true);
  lc.setLed(0, 5, 2, true);
  lc.setLed(0, 5, 3, true);
  lc.setLed(0, 5, 4, true);
  lc.setLed(0, 5, 5, true);
  lc.setLed(0, 5, 6, true);
  lc.setLed(0, 6, 2, true);
  lc.setLed(0, 6, 3, true);
  lc.setLed(0, 6, 4, true);
  lc.setLed(0, 6, 5, true);
}

void makeSad()
{
  lc.clearAll();
  lc.setLed(0, 1, 2, true);
  lc.setLed(0, 1, 5, true);
  lc.setLed(0, 2, 1, true);
  lc.setLed(0, 2, 2, true);
  lc.setLed(0, 2, 5, true);
  lc.setLed(0, 2, 6, true);
  lc.setLed(0, 4, 3, true);
  lc.setLed(0, 4, 4, true);
  lc.setLed(0, 5, 2, true);
  lc.setLed(0, 5, 5, true);
  lc.setLed(0, 6, 1, true);
  lc.setLed(0, 6, 6, true);
  lc.setLed(0, 7, 1, true);
  lc.setLed(0, 7, 6, true);
}
void makeLoading()
{
  lc.clearAll();
  lc.setLed(0, 2, 1, true);
  lc.setLed(0, 2, 2, true);
  lc.setLed(0, 2, 5, true);
  lc.setLed(0, 2, 6, true);
  lc.setLed(0, 5, 1, true);
  lc.setLed(0, 5, 2, true);
  lc.setLed(0, 5, 3, true);
  lc.setLed(0, 5, 4, true);
  lc.setLed(0, 5, 5, true);
  lc.setLed(0, 5, 6, true);

}

void scrollMessage(String message)
{
  byte a[5] = {B01111110,
               B10001000,
               B10001000,
               B10001000,
               B01111110
              };

  byte b[5] = {B01101110,
               B10010001,
               B10010001,
               B10010001,
               B01111111
              };

  byte c[5] = {B10000001,
               B10000001,
               B10000001,
               B01000010,
               B00111100
              };

  byte d[5] = {B00111100,
               B01000010,
               B10000001,
               B10000001,
               B01111111
              };

  byte e[5] = {B10010001,
               B10010001,
               B10010001,
               B10010001,
               B11111111
              };

  byte f[5] = {B10010000,
               B10010000,
               B10010000,
               B10010000,
               B11111111
              };

  byte g[5] = {B01001110,
               B10001001,
               B10000001,
               B01000010,
               B00111100
              };

  byte h[5] = {B11111111,
               B00010000,
               B00010000,
               B00010000,
               B11111111
              };

  byte iChar[5] = {B00000000,
                   B00000000,
                   B11011111,
                   B00000000,
                   B00000000
                  };

  byte j[5] = {B10000000,
               B10000000,
               B11111110,
               B10000001,
               B10000010
              };

  byte k[5] = {B10000001,
               B01000010,
               B00100100,
               B00011000,
               B11111111
              };

  byte l[5] = {B00000001,
               B00000001,
               B00000001,
               B00000001,
               B11111111
              };

  byte m[5] = {B11111111,
               B01000000,
               B00110000,
               B01000000,
               B11111111
              };

  byte n[5] = {B11111111,
               B00001000,
               B00100000,
               B01000000,
               B11111111
              };

  byte o[5] = {B00111100,
               B01000010,
               B10000001,
               B01000010,
               B00111100
              };

  byte p[5] = {B11110000,
               B10010000,
               B10010000,
               B10010000,
               B11111111
              };

  byte q[5] = {B00111101,
               B01000110,
               B10001001,
               B01000010,
               B00111100
              };

  byte r[5] = {B11110001,
               B10010010,
               B10010100,
               B10011000,
               B11111111
              };

  byte s[5] = {B10001110,
               B10010001,
               B10010001,
               B10010001,
               B01100000
              };

  byte t[5] = {B10000000,
               B10000000,
               B11111111,
               B10000000,
               B10000000
              };

  byte u[5] = {B11111110,
               B00000001,
               B00000001,
               B00000001,
               B11111110
              };

  byte v[5] = {B11100000,
               B00011100,
               B00000001,
               B00011100,
               B11100000
              };

  byte w[5] = {B11111111,
               B00000110,
               B00011000,
               B00000110,
               B11111111
              };

  byte x[5] = {B10000001,
               B00100100,
               B00011000,
               B00100100,
               B10000001
              };

  byte y[5] = {B10000000,
               B01000000,
               B00111111,
               B01000000,
               B10000000
              };

  byte z[5] = {B11000001,
               B10100001,
               B10010001,
               B10001001,
               B10000111
              };

  int lenght = message.length();
  delay(100);
  Serial.println("SIZE is _>");
  Serial.println(lenght);

  for (int i = 0; i <= lenght; i++)
  {
    Serial.println(message.charAt(i));

    if (message.charAt(i) == 'a' || message.charAt(i) == 'A')
    {
      lc.setRow(0, 0, a[0]);
      lc.setRow(0, 1, a[1]);
      lc.setRow(0, 2, a[2]);
      lc.setRow(0, 3, a[3]);
      lc.setRow(0, 4, a[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'b' || message.charAt(i) == 'B')
    {
      lc.setRow(0, 0, b[0]);
      lc.setRow(0, 1, b[1]);
      lc.setRow(0, 2, b[2]);
      lc.setRow(0, 3, b[3]);
      lc.setRow(0, 4, b[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'c' || message.charAt(i) == 'C')
    {
      lc.setRow(0, 0, c[0]);
      lc.setRow(0, 1, c[1]);
      lc.setRow(0, 2, c[2]);
      lc.setRow(0, 3, c[3]);
      lc.setRow(0, 4, c[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'd' || message.charAt(i) == 'D')
    {
      lc.setRow(0, 0, d[0]);
      lc.setRow(0, 1, d[1]);
      lc.setRow(0, 2, d[2]);
      lc.setRow(0, 3, d[3]);
      lc.setRow(0, 4, d[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'e' || message.charAt(i) == 'E')
    {
      lc.setRow(0, 0, e[0]);
      lc.setRow(0, 1, e[1]);
      lc.setRow(0, 2, e[2]);
      lc.setRow(0, 3, e[3]);
      lc.setRow(0, 4, e[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'f' || message.charAt(i) == 'F')
    {
      lc.setRow(0, 0, f[0]);
      lc.setRow(0, 1, f[1]);
      lc.setRow(0, 2, f[2]);
      lc.setRow(0, 3, f[3]);
      lc.setRow(0, 4, f[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'g' || message.charAt(i) == 'G')
    {
      lc.setRow(0, 0, g[0]);
      lc.setRow(0, 1, g[1]);
      lc.setRow(0, 2, g[2]);
      lc.setRow(0, 3, g[3]);
      lc.setRow(0, 4, g[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'h' || message.charAt(i) == 'H')
    {
      lc.setRow(0, 0, h[0]);
      lc.setRow(0, 1, h[1]);
      lc.setRow(0, 2, h[2]);
      lc.setRow(0, 3, h[3]);
      lc.setRow(0, 4, h[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'i' || message.charAt(i) == 'I')
    {
      lc.setRow(0, 0, iChar[0]);
      lc.setRow(0, 1, iChar[1]);
      lc.setRow(0, 2, iChar[2]);
      lc.setRow(0, 3, iChar[3]);
      lc.setRow(0, 4, iChar[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'j' || message.charAt(i) == 'J')
    {
      lc.setRow(0, 0, j[0]);
      lc.setRow(0, 1, j[1]);
      lc.setRow(0, 2, j[2]);
      lc.setRow(0, 3, j[3]);
      lc.setRow(0, 4, j[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'k' || message.charAt(i) == 'K')
    {
      lc.setRow(0, 0, k[0]);
      lc.setRow(0, 1, k[1]);
      lc.setRow(0, 2, k[2]);
      lc.setRow(0, 3, k[3]);
      lc.setRow(0, 4, k[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'l' || message.charAt(i) == 'L')
    {
      lc.setRow(0, 0, l[0]);
      lc.setRow(0, 1, l[1]);
      lc.setRow(0, 2, l[2]);
      lc.setRow(0, 3, l[3]);
      lc.setRow(0, 4, l[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'm' || message.charAt(i) == 'M')
    {
      lc.setRow(0, 0, m[0]);
      lc.setRow(0, 1, m[1]);
      lc.setRow(0, 2, m[2]);
      lc.setRow(0, 3, m[3]);
      lc.setRow(0, 4, m[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'n' || message.charAt(i) == 'N')
    {
      lc.setRow(0, 0, n[0]);
      lc.setRow(0, 1, n[1]);
      lc.setRow(0, 2, n[2]);
      lc.setRow(0, 3, n[3]);
      lc.setRow(0, 4, n[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'o' || message.charAt(i) == 'O')
    {
      lc.setRow(0, 0, o[0]);
      lc.setRow(0, 1, o[1]);
      lc.setRow(0, 2, o[2]);
      lc.setRow(0, 3, o[3]);
      lc.setRow(0, 4, o[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'p' || message.charAt(i) == 'P')
    {
      lc.setRow(0, 0, p[0]);
      lc.setRow(0, 1, p[1]);
      lc.setRow(0, 2, p[2]);
      lc.setRow(0, 3, p[3]);
      lc.setRow(0, 4, p[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'q' || message.charAt(i) == 'Q')
    {
      lc.setRow(0, 0, q[0]);
      lc.setRow(0, 1, q[1]);
      lc.setRow(0, 2, q[2]);
      lc.setRow(0, 3, q[3]);
      lc.setRow(0, 4, q[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'r' || message.charAt(i) == 'R')
    {
      lc.setRow(0, 0, r[0]);
      lc.setRow(0, 1, r[1]);
      lc.setRow(0, 2, r[2]);
      lc.setRow(0, 3, r[3]);
      lc.setRow(0, 4, r[4]);
      delay(300);
    }
    else if (message.charAt(i) == 's' || message.charAt(i) == 'S')
    {
      lc.setRow(0, 0, s[0]);
      lc.setRow(0, 1, s[1]);
      lc.setRow(0, 2, s[2]);
      lc.setRow(0, 3, s[3]);
      lc.setRow(0, 4, s[4]);
      delay(300);
    }
    else if (message.charAt(i) == 't' || message.charAt(i) == 'T')
    {
      lc.setRow(0, 0, t[0]);
      lc.setRow(0, 1, t[1]);
      lc.setRow(0, 2, t[2]);
      lc.setRow(0, 3, t[3]);
      lc.setRow(0, 4, t[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'u' || message.charAt(i) == 'U')
    {
      lc.setRow(0, 0, u[0]);
      lc.setRow(0, 1, u[1]);
      lc.setRow(0, 2, u[2]);
      lc.setRow(0, 3, u[3]);
      lc.setRow(0, 4, u[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'v' || message.charAt(i) == 'V')
    {
      lc.setRow(0, 0, v[0]);
      lc.setRow(0, 1, v[1]);
      lc.setRow(0, 2, v[2]);
      lc.setRow(0, 3, v[3]);
      lc.setRow(0, 4, v[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'w' || message.charAt(i) == 'W')
    {
      lc.setRow(0, 0, w[0]);
      lc.setRow(0, 1, w[1]);
      lc.setRow(0, 2, w[2]);
      lc.setRow(0, 3, w[3]);
      lc.setRow(0, 4, w[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'x' || message.charAt(i) == 'X')
    {
      lc.setRow(0, 0, x[0]);
      lc.setRow(0, 1, x[1]);
      lc.setRow(0, 2, x[2]);
      lc.setRow(0, 3, x[3]);
      lc.setRow(0, 4, x[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'y' || message.charAt(i) == 'Y')
    {
      lc.setRow(0, 0, y[0]);
      lc.setRow(0, 1, y[1]);
      lc.setRow(0, 2, y[2]);
      lc.setRow(0, 3, y[3]);
      lc.setRow(0, 4, y[4]);
      delay(300);
    }
    else if (message.charAt(i) == 'z' || message.charAt(i) == 'Z')
    {
      lc.setRow(0, 0, z[0]);
      lc.setRow(0, 1, z[1]);
      lc.setRow(0, 2, z[2]);
      lc.setRow(0, 3, z[3]);
      lc.setRow(0, 4, z[4]);
      delay(300);
    }
  }
}

