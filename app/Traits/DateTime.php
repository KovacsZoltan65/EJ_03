<?php

/**
 * Dátum függvények
 * 
 * @author Kovács Zoltán <kovacs.zoltan@softc.hu>
 * @date 2023-08-01
 */

namespace App\Traits;

use Illuminate\Support\Carbon;

trait DateTiem
{
    /**
     * The default format for dates and times.
     *
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';
    
    /**
     * The localization for date and time formatting.
     *
     * @var string
     */
    protected $localization = 'hu';
    
    /**
     * The timezone name for date and time calculations.
     *
     * @var string
     */
    protected $timezone_name = 'Europe/Budapest';
    
    /**
     * Returns the current date and time in the specified format.
     *
     * @param string|null $format The format of the date and time. If not provided, the default format is used.
     * @return string The current date and time in the specified format.
     */
    public function actualDate(string $format = null): string
    {
        // If no format is provided, use the default format.
        if( is_null($format) ){
            $format = $this->format;
        }
        
        // Return the current date and time in the specified format.
        return Carbon::now()->format($format);
    }
    
    /*
    * ============================================
    * Hét függvényei
    * ============================================
    */

    /**
     * Returns the start date of the last week in the specified format.
     *
     * @param string|null $format The format of the date and time. If not provided, the default format is used.
     * @return string The start date of the last week in the specified format.
     */
    public function startLastWeek(string $format = null): string
    {
        // If no format is provided, use the default format.
        if( is_null($format) ){
            $format = $this->format;
        }
        
        // Return the start date of the last week in the specified format.
        return Carbon::now()->subWeek()->startOfWeek()->format($format);
    }
    
    public function endOfLastWeek(string $format = null): string
    {
        if( is_null($format) ) {
            $format = $this->format;
        }
        
        return Carbon::now()->subWeek()->endOfWeek()->format($format);
    }
    
    /**
     * ============================================
     * Hónap függvényei
     * ============================================
     */

    public function startOfThisMonth(string $format = null): string{
        if( is_null($format) ) {
            $format = $this->format;
        }

        return Carbon::now()->startOfMonth()->format($format);
    }

    public function endOfThisMonth(string $format = null): string{
        if( is_null($format) ) {
            $format = $this->format;
        }
        return Carbon::now()->endOfMonth()->format($format);
    }

    public function endOfLastMonth(string $format = null): string{
        if( is_null($format) ) {
            $format = $this->format;
        }

        return Carbon::now()->subMonth()->startOfMonth()->format($format);
    }

    public function endOfNextMonth(string $format = null): string{
        if( is_null($format) ) {
            $format = $this->format;
        }
        return Carbon::now()->addMonth()->startOfMonth()->format($format);
    }

    public function startOfNextMonth(string $format = null): string{
        if( is_null($format) ) {
            $format = $this->format;
        }
        return Carbon::now()->addMonth()->startOfMonth()->format($format);
    }

    /*
     * ============================================
     * Relációk
     * ============================================
     */

    /**
     * Compares two dates and returns true if they are equal.
     *
     * @param string $date_01 The first date to compare.
     * @param string $date_02 The second date to compare.
     * @param string|null $format The format of the dates. If not provided, the default format is used.
     * @return bool True if the dates are equal, false otherwise.
     */
    public function compareDates(string $date_01, string $date_02, string $format = null): bool
    {
        if( is_null($format) ) {
            $format = $this->format;
        }

        $d_date_01 = Carbon::createFromFormat($format, $date_01);
        $d_date_02 = Carbon::createFromFormat($format, $date_02);

        return ( $d_date_01->equalTo($d_date_02) );
    }
    
    /**
     * Calculates the difference in days between two dates.
     *
     * @param string $date_01 The first date.
     * @param string $date_02 The second date.
     * @param string|null $format The format of the dates. If not provided, the default format is used.
     * @return int The difference in days between the two dates.
     */
    public function getDaysDifference(string $date_01, string $date_02, string $format = null): int
    {
        if( is_null($format) ) {
            $format = $this->format;
        }

        // Create two Carbon instances from the given dates in the specified format.
        $d_date_01 = Carbon::createFromFormat($format, $date_01);
        $d_date_02 = Carbon::createFromFormat($format, $date_02);

        // Calculate the difference in days between the two Carbon instances.
        return $d_date_01->diffInDays($d_date_02);
    }

    /*
     * ============================================
     * Logikai
     * ============================================
     */

    /**
     * Checks if a date is a weekday.
     *
     * @param string $date The date to check.
     * @param string|null $localization The localization for weekend days. If not provided, the default localization is used.
     * @return bool True if the date is a weekday, false otherwise.
     */
    public function isWeekday(string $date, string $localization = null): bool
    {
        if( is_null($localization) ) {
            $localization = $this->localization;
        }

        return Carbon::parse($date)->isWeekday($localization);
    }
    
    /**
     * Checks if a date is a weekend day.
     *
     * @param string $date The date to check.
     * @param string|null $localization The localization for weekend days. If not provided, the default localization is used.
     * @return bool True if the date is a weekend day, false otherwise.
     */
    public function isWeekend(string $date, string $localization = null): bool
    {
        // If no localization is provided, use the default localization.
        if( is_null($localization) ) {
            $localization = $this->localization;
        }

        // Parse the date and check if it is a weekend day using the given localization.
        return Carbon::parse($date)->isWeekend($localization);
    }
    
    /**
     * Checks if a string is a valid date in a specific format.
     *
     * @param string $date The date to check.
     * @param string $format The format of the date.
     * @return bool True if the date is valid, false otherwise.
     */
    public function isDate(string $date, string $format): bool
    {
        // Try to create a DateTime object from the date string and the given format
        if (\DateTime::createFromFormat($format, $date) === false) {
            // If the creation of the DateTime object fails, the date is not valid
            return false;
        }
        
        // The date is valid
        return true;
    }


    /*
     * ============================================
     * Lekérések és beállítások
     * ============================================
     */
    
    /**
     * Retrieves an array of month names in the specified format.
     *
     * @param string|null $format The format of the month names. If not provided, the default format is used.
     * @return array An array of month names in the specified format.
     */
    public function getMonthNames(string $format = null): array {
        // If no format is provided, use the default format.
        if( is_null($format) ) {
            $format = $this->format;
        }

        // Get the month names in the specified format.
        // The formatLocalized() method is used to get the localized month names.
        // The '%B' format specifier is used to get the full month name.
        // The locale() method is used to specify the locale for the month names.
        return Carbon::now()->locale($this->locale)->formatLocalized('%B', $format);
    }

    /**
     * Retrieves the name of a month given its number.
     *
     * @param int $month The number of the month (1-12).
     * @param string|null $format The format of the month name. If not provided, the default format is used.
     * @return string The name of the month in the specified format.
     */
    public function getMonthName(int $month, string $format = null): string {
        // Get the month names in the specified format.
        // The $month - 1 is used because the months are zero-based in the array.
        return $this->getMonthNames($format)[$month - 1];
    }

    /**
     * Lekéri a hét napjainak tömbjét, beleértve a vasárnapot is a megadott formátumban.
     *
     * @param string|null $format A hétköznapok nevének formátuma. Ha nincs megadva, a rendszer az alapértelmezett formátumot használja.
     * @return array Hétnapnevek tömbje, beleértve a vasárnapot is a megadott formátumban.
     */
    public function getWeekdayNamesIncludingSunday(string $format = null): array {
        // Ha nincs megadva formátum, használja az alapértelmezett formátumot.
        if( is_null($format) ) {
            $format = $this->format;
        }

        // Szerezze be a hét napjait, beleértve a vasárnapot is, a megadott formátumban.
        // A formatLocalized() metódus a honosított hétköznapok nevének lekérésére szolgál.
        // A '%A' formátummeghatározó a hétköznapok teljes nevének lekérésére szolgál.
        // A locale() metódus a hét napjai nevének területi beállítására szolgál.
        // Az eredmény a hét napjainak tömbje, beleértve a vasárnapot is.
        return Carbon::now()->locale($this->locale)->formatLocalized('%A', $format);
    }

     /**
     * Returns the name of the weekday for the given date.
     *
     * @param string $date The date to get the weekday name for.
     * @param string|null $format The format of the date. If not provided, the default format is used.
     * @param string|null $localization The localization for the weekday name. If not provided, the default localization is used.
     * @return string The name of the weekday for the given date.
     */
    public function getWeekdayName(string $date, string $format = null, string $localization = null): string
    {
        // If no format is provided, use the default format.
        if( is_null($format) ) {
            $format = $this->format;
        }
        
        // If no localization is provided, use the default localization.
        if( is_null($localization) ) {
            $localization = $this->localization;
        }
        
        // Parse the date with the given format.
        $d_date = Carbon::parse($date);
        
        // Get the name of the weekday for the parsed date with the given localization.
        return $d_date->formatLocalized('%A', $localization);
    }

    public function getWeekdayNames(string $date, string $localization = null): array
    {   
        // If no localization is provided, use the default localization.
        if( is_null($localization) ) {
            $localization = $this->localization;
        }
        
        // Parse the date with the given format.
        $d_date = Carbon::parse($date);
        
        // Get the names of the weekdays for the parsed date with the given localization.
        return $d_date->formatLocalized('%A', $localization)->toArray();
    }

    /**
     * Returns the name of the timezone currently set.
     *
     * @return string The name of the timezone.
     */
    public function getTimezoneName(): string{
        return Carbon::now()->tzName;
    }

    /**
     * Sets the timezone name.
     *
     * @param string $timezone_name The name of the timezone.
     * @return void
     */
    public function setTimezoneName(string $timezone_name): void{
        $this->timezone_name = $timezone_name;
    }

    /**
     * Returns the format of the date currently set.
     *
     * @return string The format of the date.
     */
    public function getFormat(): string{
        return $this->format;
    }

    /**
     * Sets the format of the date.
     *
     * @param string $format The format of the date.
     * @return void
     */
    public function setFormat(string $format): void{
        $this->format = $format;
    }

    /**
     * Returns the localization currently set.
     *
     * @return string The localization.
     */
    public function getLocalization(): string{
        return $this->localization;
    }
    
    /**
     * Sets the localization.
     *
     * @param string $localization The localization.
     * @return void
     */
    public function setLocalization(string $localization): void{
        $this->localization = $localization;
    }
}
