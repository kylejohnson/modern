
/* This script and many more are available free online at
The JavaScript Source :: http://javascript.internet.com
Created by: Oded Arbel :: http://geek.co.il/wp/ */

/**
* Extension of the JavaScript internal Date object to allow various formatting of
* date/time values.
* This implementation was designed to be compliant with the formatting of the
* Java class library's SimpleDateFormat object, with the addition of the 'x' format
* option to show number of seconds since the epoch (1/1/1970 00:00).
*
* See http://java.sun.com/j2se/1.5.0/docs/api/java/text/SimpleDateFormat.html for
* full details.
*
* (c) Copyright 2006 - Oded Arbel
* (c) Portions copyright 2006 - Jack Slocum
*/

// Static definition of Month names
Date.MONTH_NAMES = [
"January", "February", "March",
"April", "May", "June",
"July", "August", "September",
"October", "November", "December" ];

// Static definition of weekday names
Date.WEEKDAY_NAMES = [
"Sunday", "Monday", "Tuesday",
"Wednesday", "Thursday", "Friday",
"Saturday" ];

// clone the current date object and return a different object with identical value
Date.prototype.clone = function () {
  return new Date(this.getTime());
}

// clear the time information from this date and return it
Date.prototype.clearTime = function () {
  this.setHours(0); this.setMinutes(0);
  this.setSeconds(0); this.setMilliseconds(0);
  return this;
}

// return the last day of this month
Date.prototype.lastDay = function () {
  var tempDate = this.clone();
  tempDate.setMonth(tempDate.getMonth()+1);
  tempDate.setDate(0);
  return tempDate.getDate();
}

// return number of days since start of year
Date.prototype.getYearDay = function () {
  var today = new Date(this);
  today.setHours(0); today.setMinutes(0); today.setSeconds(0);
  var tempDate = new Date(today);
  // set start of year
  tempDate.setDate(1);
  tempDate.setMonth(0);
  return Math.round(
  (today.getTime() - tempDate.getTime())
  / 86400 / 1000) + 1; // Jan/1 is day 1
}

// add format() to Date
Date.prototype.format = function(formatString) {
  var out = new String();
  var token = ""
  for (var i = 0; i < formatString.length; i++) {
    if (formatString.charAt(i) == token.charAt(0)) {
      token = token.concat(formatString.charAt(i));
      continue;
    }
    out = out.concat(this.convertToken(token));
    token = formatString.charAt(i);
  }
  return out + this.convertToken(token);
}

// internal call to map tokens to the date data
Date.prototype.convertToken = function (str) {
  switch(str.charAt(0)) {
    case 'y': // set year
      if (str.length > 2)
      return this.getFullYear();
      return this.getFullYear().toString().substring(2);
    case 'd': // set date
      return Date.zeroPad(this.getDate(),str.length);
    case 'D': // set day in year
      return this.getYearDay();
    case 'a':
      return this.getHours() > 11 ? "PM" : "AM";
    case 'H': // set hours
      return Date.zeroPad(this.getHours(),str.length);
    case 'h':
      return Date.zeroPad(this.get12Hours(),str.length);
    case 'm': // set minutes
      return Date.zeroPad(this.getMinutes(),2);
    case 's': // set secondes
      return Date.zeroPad(this.getSeconds(),2);
    case 'S': // set milisecondes
      return Date.zeroPad(this.getMilliseconds(),str.length);
    case 'x': // set epoch time
      return this.getTime();
    case 'Z': // set time zone
      return (this.getTimezoneOffset() / 60) + ":" +
      Date.zeroPad(this.getTimezoneOffset() % 60,2);
    case 'M': // set month
      if (str.length > 3) return this.getFullMonthName();
      if (str.length > 2) return this.getShortMonthName();
      return Date.zeroPad(this.getMonth()+1,str.length);
    case 'E': // set dow
      if (str.length > 3) return this.getDOWName();
      if (str.length > 1) return this.getShortDOWName();
      return this.getDay();
      default:
      return str;
  }
}

// Retreive the month's name in english
Date.prototype.getFullMonthName = function() {
  return Date.MONTH_NAMES[this.getMonth()];
}

// Retreive the abberviated month name in english
Date.prototype.getShortMonthName = function() {
  return Date.MONTH_NAMES[this.getMonth()].substring(0,3);
}

// Retreive the week day name in english
Date.prototype.getDOWName = function () {
  return Date.WEEKDAY_NAMES[this.getDay()];
}

// Retreive the abberviated week day name in english
Date.prototype.getShortDOWName = function () {
  return Date.WEEKDAY_NAMES[this.getDay()].substring(0,3);
}

// Retreive the hour in a 12 hour clock (without the AM/PM specification)
Date.prototype.get12Hours = function () {
  return this.getHours() == 0 ? 12 :
  (this.getHours() > 12 ? this.getHours() - 12 : this.getHours());
}

// helper function to add required zero characters to fixed length fields
Date.zeroPad = function(num, width) {
  num = num.toString();
  while (num.length < width)
  num = "0" + num;
  return num;
}






