<?php

namespace App\Enums;

enum Country: string
{
    case ALBANIA = "Albania";
    case ANDORRA = "Andorra";
    case ANGUILLA = "Anguilla";
    case ANTIGUA_AND_BARBUDA = "Antigua and Barbuda";
    case ARGENTINA = "Argentina";
    case ARUBA = "Aruba";
    case AUSTRIA = "Austria";
    case BAHAMAS = "Bahamas";
    case BARBADOS = "Barbados";
    case BELARUS = "Belarus";
    case BELGIUM = "Belgium";
    case BELIZE = "Belize";
    case BERMUDA = "Bermuda";
    case BOLIVIA = "Bolivia";
    case BOSNIA_AND_HERZEGOVINA = "Bosnia and Herzegovina";
    case BRAZIL = "Brazil";
    case BRITISH_VIRGIN_ISLANDS = "British Virgin Islands";
    case BULGARIA = "Bulgaria";
    case CANADA = "Canada";
    case CARIBBEAN_NETHERLANDS = "Caribbean Netherlands";
    case CAYMAN_ISLANDS = "Cayman Islands";
    case CHILE = "Chile";
    case COLOMBIA = "Colombia";
    case COSTA_RICA = "Costa Rica";
    case CROATIA = "Croatia";
    case CUBA = "Cuba";
    case CURACAO = "Curaçao";
    case CYPRUS = "Cyprus";
    case CZECHIA = "Czechia";
    case DENMARK = "Denmark";
    case DOMINICA = "Dominica";
    case DOMINICAN_REPUBLIC = "Dominican Republic";
    case ECUADOR = "Ecuador";
    case EL_SALVADOR = "El Salvador";
    case ESTONIA = "Estonia";
    case FALKLAND_ISLANDS = "Falkland Islands";
    case FAROE_ISLANDS = "Faroe Islands";
    case FINLAND = "Finland";
    case FRANCE = "France";
    case FRENCH_GUIANA = "French Guiana";
    case GERMANY = "Germany";
    case GIBRALTAR = "Gibraltar";
    case GREECE = "Greece";
    case GREENLAND = "Greenland";
    case GRENADA = "Grenada";
    case GUADELOUPE = "Guadeloupe";
    case GUATEMALA = "Guatemala";
    case GUERNSEY = "Guernsey";
    case GUYANA = "Guyana";
    case HAITI = "Haiti";
    case HONDURAS = "Honduras";
    case HUNGARY = "Hungary";
    case ICELAND = "Iceland";
    case IRELAND = "Ireland";
    case ISLE_OF_MAN = "Isle of Man";
    case ITALY = "Italy";
    case JAMAICA = "Jamaica";
    case JERSEY = "Jersey";
    case KOSOVO = "Kosovo";
    case LATVIA = "Latvia";
    case LIECHTENSTEIN = "Liechtenstein";
    case LITHUANIA = "Lithuania";
    case LUXEMBOURG = "Luxembourg";
    case MALTA = "Malta";
    case MARTINIQUE = "Martinique";
    case MEXICO = "Mexico";
    case MOLDOVA = "Moldova";
    case MONACO = "Monaco";
    case MONTENEGRO = "Montenegro";
    case MONTSERRAT = "Montserrat";
    case NETHERLANDS = "Netherlands";
    case NICARAGUA = "Nicaragua";
    case NORTH_MACEDONIA = "North Macedonia";
    case NORWAY = "Norway";
    case PANAMA = "Panama";
    case PARAGUAY = "Paraguay";
    case PERU = "Peru";
    case POLAND = "Poland";
    case PORTUGAL = "Portugal";
    case PUERTO_RICO = "Puerto Rico";
    case ROMANIA = "Romania";
    case RUSSIA = "Russia";
    case SAINT_BARTHELEMY = "Saint Barthélemy";
    case SAINT_KITTS_AND_NEVIS = "Saint Kitts and Nevis";
    case SAINT_LUCIA = "Saint Lucia";
    case SAINT_MARTIN = "Saint Martin";
    case SAINT_PIERRE_AND_MIQUELON = "Saint Pierre and Miquelon";
    case SAINT_VINCENT_AND_THE_GRENADINES = "Saint Vincent and the Grenadines";
    case SAN_MARINO = "San Marino";
    case SERBIA = "Serbia";
    case SINT_MAARTEN = "Sint Maarten";
    case SLOVAKIA = "Slovakia";
    case SLOVENIA = "Slovenia";
    case SPAIN = "Spain";
    case SURINAME = "Suriname";
    case SVALBARD_AND_JAN_MAYEN = "Svalbard and Jan Mayen";
    case SWEDEN = "Sweden";
    case SWITZERLAND = "Switzerland";
    case TRINIDAD_AND_TOBAGO = "Trinidad and Tobago";
    case TURKS_AND_CAICOS_ISLANDS = "Turks and Caicos Islands";
    case UKRAINE = "Ukraine";
    case UNITED_KINGDOM = "United Kingdom";
    case UNITED_STATES = "United States";
    case URUGUAY = "Uruguay";
    case VATICAN_CITY = "Vatican City";
    case VENEZUELA = "Venezuela";
    case VIRGIN_ISLANDS = "Virgin Islands";
    case ALAND_ISLANDS = "Åland Islands";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
