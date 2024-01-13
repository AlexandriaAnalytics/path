<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Country extends Enum
{
    const ALBANIA = "Albania";
    const ANDORRA = "Andorra";
    const ANGUILLA = "Anguilla";
    const ANTIGUA_AND_BARBUDA = "Antigua and Barbuda";
    const ARGENTINA = "Argentina";
    const ARUBA = "Aruba";
    const AUSTRIA = "Austria";
    const BAHAMAS = "Bahamas";
    const BARBADOS = "Barbados";
    const BELARUS = "Belarus";
    const BELGIUM = "Belgium";
    const BELIZE = "Belize";
    const BERMUDA = "Bermuda";
    const BOLIVIA = "Bolivia";
    const BOSNIA_AND_HERZEGOVINA = "Bosnia and Herzegovina";
    const BRAZIL = "Brazil";
    const BRITISH_VIRGIN_ISLANDS = "British Virgin Islands";
    const BULGARIA = "Bulgaria";
    const CANADA = "Canada";
    const CARIBBEAN_NETHERLANDS = "Caribbean Netherlands";
    const CAYMAN_ISLANDS = "Cayman Islands";
    const CHILE = "Chile";
    const COLOMBIA = "Colombia";
    const COSTA_RICA = "Costa Rica";
    const CROATIA = "Croatia";
    const CUBA = "Cuba";
    const CURACAO = "Curaçao";
    const CYPRUS = "Cyprus";
    const CZECHIA = "Czechia";
    const DENMARK = "Denmark";
    const DOMINICA = "Dominica";
    const DOMINICAN_REPUBLIC = "Dominican Republic";
    const ECUADOR = "Ecuador";
    const EL_SALVADOR = "El Salvador";
    const ESTONIA = "Estonia";
    const FALKLAND_ISLANDS = "Falkland Islands";
    const FAROE_ISLANDS = "Faroe Islands";
    const FINLAND = "Finland";
    const FRANCE = "France";
    const FRENCH_GUIANA = "French Guiana";
    const GERMANY = "Germany";
    const GIBRALTAR = "Gibraltar";
    const GREECE = "Greece";
    const GREENLAND = "Greenland";
    const GRENADA = "Grenada";
    const GUADELOUPE = "Guadeloupe";
    const GUATEMALA = "Guatemala";
    const GUERNSEY = "Guernsey";
    const GUYANA = "Guyana";
    const HAITI = "Haiti";
    const HONDURAS = "Honduras";
    const HUNGARY = "Hungary";
    const ICELAND = "Iceland";
    const IRELAND = "Ireland";
    const ISLE_OF_MAN = "Isle of Man";
    const ITALY = "Italy";
    const JAMAICA = "Jamaica";
    const JERSEY = "Jersey";
    const KOSOVO = "Kosovo";
    const LATVIA = "Latvia";
    const LIECHTENSTEIN = "Liechtenstein";
    const LITHUANIA = "Lithuania";
    const LUXEMBOURG = "Luxembourg";
    const MALTA = "Malta";
    const MARTINIQUE = "Martinique";
    const MEXICO = "Mexico";
    const MOLDOVA = "Moldova";
    const MONACO = "Monaco";
    const MONTENEGRO = "Montenegro";
    const MONTSERRAT = "Montserrat";
    const NETHERLANDS = "Netherlands";
    const NICARAGUA = "Nicaragua";
    const NORTH_MACEDONIA = "North Macedonia";
    const NORWAY = "Norway";
    const PANAMA = "Panama";
    const PARAGUAY = "Paraguay";
    const PERU = "Peru";
    const POLAND = "Poland";
    const PORTUGAL = "Portugal";
    const PUERTO_RICO = "Puerto Rico";
    const ROMANIA = "Romania";
    const RUSSIA = "Russia";
    const SAINT_BARTHELEMY = "Saint Barthélemy";
    const SAINT_KITTS_AND_NEVIS = "Saint Kitts and Nevis";
    const SAINT_LUCIA = "Saint Lucia";
    const SAINT_MARTIN = "Saint Martin";
    const SAINT_PIERRE_AND_MIQUELON = "Saint Pierre and Miquelon";
    const SAINT_VINCENT_AND_THE_GRENADINES = "Saint Vincent and the Grenadines";
    const SAN_MARINO = "San Marino";
    const SERBIA = "Serbia";
    const SINT_MAARTEN = "Sint Maarten";
    const SLOVAKIA = "Slovakia";
    const SLOVENIA = "Slovenia";
    const SPAIN = "Spain";
    const SURINAME = "Suriname";
    const SVALBARD_AND_JAN_MAYEN = "Svalbard and Jan Mayen";
    const SWEDEN = "Sweden";
    const SWITZERLAND = "Switzerland";
    const TRINIDAD_AND_TOBAGO = "Trinidad and Tobago";
    const TURKS_AND_CAICOS_ISLANDS = "Turks and Caicos Islands";
    const UKRAINE = "Ukraine";
    const UNITED_KINGDOM = "United Kingdom";
    const UNITED_STATES = "United States";
    const URUGUAY = "Uruguay";
    const VATICAN_CITY = "Vatican City";
    const VENEZUELA = "Venezuela";
    const VIRGIN_ISLANDS = "Virgin Islands";
    const ALAND_ISLANDS = "Åland Islands";

    public static function getOptions()
    {
        $options = [];
        foreach (self::getValues() as $value) {
            $options[$value] = $value;
        }
        return $options;
    }
}
