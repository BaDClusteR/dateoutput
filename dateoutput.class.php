<?php

/* vim: set ts=4 sw=4 sts=4 et: */
/**
 * Class DateOutput
 *
 * @author     BaD ClusteR <admin@badcluster.ru>
 * @license    http://www.gnu.org/licenses/gpl.html GPL license agreement
 * @version    1.0
 * @link       http://badcluster.ru
 *
 * Вывод даты и времени в более понятном человеку виде (вместо "11.12.2013 11:48" выводится например "вчера в 11:48"
 * или "11 ноября 2013г. в 11:48"), а также разницы между двумя метками времени (например, "57 секунд назад" или
 * "4 месяца назад").
 *
 */

class DateOutput
{
	/**
	 * @var string Кодировка, в которой будет выводиться результат
	 */
	private $encoding = "UTF-8";
	/**
	 * @var string Кодировка данного модуля
	 */
	private $core_enc = "UTF-8";
	/**
	 * @var bool Выводить ли секунды при форматированном выводе даты и времени
	 */
	private $seconds = false;
	/**
	 * @var bool Выводить ли время при форматированном выводе даты
	 */
	private $time = true;
	/**
	 * @var int TimeStamp, который выводится в форматированном виде и разница с которым будет высчитываться при выводе разницы (ф-ция ago)
	 */
	private $ts;
	/**
	 * @var string Задается, если нужно выводить дату либо считать разницу во временной зоне, отличной от установленной
	 */
	private $timezone = "";

	function __construct($ts, $encoding = "UTF-8", $timezone = "", $time = true, $seconds = false)
	{
		if (!is_int($ts) && intval($ts) != $ts)
			$ts = strtotime($ts);
		$this->encoding = $encoding;
		$this->seconds = $seconds;
		$this->time = $time;
		$this->timezone = $timezone;
		$this->ts = $ts;
	}

	/**
	 * Получить название месяца по его номеру.
	 *
	 * @param int $index Номер месяца
	 *
	 * @return string
	 * @since v. 1.0
	 */
	private function month_name($index)
	{
		switch ($index)
		{
			case 1: return "января";
			case 2: return "февраля";
			case 3: return "марта";
			case 4: return "апреля";
			case 5: return "мая";
			case 6: return "июня";
			case 7: return "июля";
			case 8: return "августа";
			case 9: return "сентября";
			case 10: return "октября";
			case 11: return "ноября";
			case 12: return "декабря";
		}
		return "";
	}

	/**
	 * Вернуть временную зону, заданную в классе. Если временная зона не задавалась, возвращается временная зона,
	 * установленная в PHP.
	 *
	 * @return string
	 * @since v. 1.0
	 */
	public function getTimezone()
	{
		return (!empty($this->timezone)) ? $this->timezone : date_default_timezone_get();
	}

	/**
	 * Установить свою временную зону для вычисления даты и разницы между датами.
	 *
	 * @param string $identifier Идентификатор устанавливаемой временной зоны
	 *
	 * @since v. 1.0
	 */
	public function setTimezone($identifier)
	{
		$this->timezone = $identifier;
	}

	/**
	 * Получить название дня недели по его номеру.
	 *
	 * @param int $index Номер дня недели
	 *
	 * @return string
	 * @since v. 1.0
	 */
	private function weekday_name($index)
	{
		switch ($index)
		{
			case 0: return " воскресенье";
			case 1: return " понедельник";
			case 2: return "о вторник";
			case 3: return " среду";
			case 4: return " четверг";
			case 5: return " пятницу";
			case 6: return " субботу";
		}
		return "";
	}

	/**
	 * Возвращает строковое значение времени для заданной метки.
	 *
	 * @return string
	 * @since v. 1.0
	 */
	private function getTime()
	{
		if (!$this->time)
			return "";
		$dt_format = ($this->seconds) ? "H:i:s" : "H:i";
		return " в " . date($dt_format, $this->ts);
	}

	/**
	 * Вывод даты в форматированнном виде.
	 *
	 * @return string
	 * @since v. 1.0
	 */
	public function generate()
	{
		$tz = date_default_timezone_get();
		if (!empty($this->timezone) && $this->timezone != $tz)
			date_default_timezone_set($this->timezone);
		$now = getdate();
		$time = getdate($this->ts);
		if ($time['year'] == $now['year'] || abs(time() - $this->ts) <= 3600 * 24 * 7)
		{
			if ($time['yday'] == $now['yday'])
				$result = "сегодня" . $this->getTime();
			elseif ($time['yday'] == $now['yday'] - 1)
				$result = "вчера" . $this->getTime();
			elseif ($time['yday'] == $now['yday'] - 2)
				$result = "позавчера" . $this->getTime();
			elseif ($time['yday'] == $now['yday'] + 1)
				$result = "завтра" . $this->getTime();
			elseif ($time['yday'] == $now['yday'] + 2)
				$result = "послезавтра" . $this->getTime();
			elseif (abs($time['yday'] - $now['yday']) <= 6)
				$result = "в" . $this->weekday_name($time['wday']) . $this->getTime();
			else
				$result = $time['mday'] . " " . $this->month_name($time['mon']) . $this->getTime();
		}
		else
			$result = $time['mday'] . " " . $this->month_name($time['mon']) . " " . $time['year'] . " г." . $this->getTime();
		if (!empty($this->timezone) && $this->timezone != $tz)
			date_default_timezone_set($tz);
		return ($this->core_enc == $this->encoding) ? $result : iconv($this->core_enc, $this->encoding, $result);
	}

	/**
	 * Возвращает слово в нужном падеже в зависимости от количества.
	 *
	 * @param string $type  Индекс ассоциативного массива $cases, откуда будет браться нужное слово.
	 * @param int    $count Количество
	 *
	 * @return string
	 * @since v. 1.0
	 */
	private function word_case($type, $count)
	{
		$cases = array(
			'year' => array("год", "года", "лет"),
			'month' => array("месяц", "месяца", "месяцев"),
			'week' => array("неделю", "недели", "недель"),
			'day' => array("день", "дня", "дней"),
			'hour' => array("час", "часа", "часов"),
			'minute' => array("минуту", "минуты", "минут"),
			'second' => array("секунду", "секунды", "секунд")
		);
		if ($count == 0)
			return " " . $cases[$type][2];
		elseif ($count < 0)
			$count = -$count;
		if (($count % 100) >= 10 && ($count % 100) <= 20)
			return " " . $cases[$type][2];
		switch ($count % 10)
		{
			case 1: return " " . $cases[$type][0];
			case 2:
			case 3:
			case 4: return " " . $cases[$type][1];
			default: return " " . $cases[$type][2];
		}
	}

	/**
	 * Возвращает число секунд, пройденных с начала заданной в массиве $dt даты.
	 *
	 * @param array $dt Ассоциативный массив с элементами 'hours', 'minutes' и 'seconds'
	 *
	 * @return int
	 * @since v. 1.0
	 */
	private function day_ts($dt)
	{
		return $dt['hours'] * 3600 + $dt['minutes'] * 60 + $dt['seconds'];
	}

	/**
	 * Вывод в удобочитаемом виде разницы между двумя метками времени
	 *
	 * @param int  $currtime Момент времени, от которого ведется отсчет (если равна нулю, берется текущее время)
	 * @param bool $weeks    Выводить ли результат в неделях, если разница больше 7 дней, но меньше месяца
	 *
	 * @return string
	 * @since v. 1.0
	 */
	public function ago($weeks = true, $currtime = 0)
	{
		if ($currtime == 0)
			$currtime = time();
		$dt = $this->ts - $currtime;
		$result = ($dt < 0) ? "{time} назад" : "через {time}";
		$dt = abs($dt);
		if ($dt < 60)
			$dt = (($dt > 1) ? $dt : "") . $this->word_case("second", $dt);
		elseif ($dt < 3600)
		{
			$dt = floor($dt / 60);
			$dt = (($dt > 1) ? $dt : "") . $this->word_case("minute", $dt);
		}
		elseif ($dt < 3600 * 24)
		{
			$dt = floor($dt / 3600);
			$dt = (($dt > 1) ? $dt : "") . $this->word_case("hour", $dt);
		}
		elseif ($dt < 3600 * 24 * 7)
		{
			$dt = floor($dt / (3600 * 24));
			$dt = (($dt > 1) ? $dt : "") . $this->word_case("day", $dt);
		}
		elseif ($dt < 3600 * 24 * 28 && $weeks)
		{
			$dt = floor($dt / (3600 * 24 * 7));
			$dt = (($dt > 1) ? $dt : "") . $this->word_case("week", $dt);
		}
		else
		{
			$temp = "";
			if (!empty($this->timezone))
			{
				$temp = date_default_timezone_get();
				date_default_timezone_set($this->timezone);
			}
			$mindate = getdate((($this->ts < $currtime) ? $this->ts : $currtime));
			$maxdate = getdate((($this->ts < $currtime) ? $currtime : $this->ts));
			if (!empty($this->timzeone))
				date_default_timezone_set($temp);
			if (($maxdate['year'] - $mindate['year'] > 1) || (($maxdate['year'] - $mindate['year'] == 1) && (($maxdate['yday'] > $mindate['yday']) || (($maxdate['yday'] == $mindate['yday']) && ($this->day_ts($maxdate) >= $this->day_ts($mindate))))))
			{
				$dt = $maxdate['year'] - $mindate['year'];
				if ($maxdate['yday'] < $mindate['yday'])
					$dt--;
				$dt = (($dt > 1) ? $dt : "") . $this->word_case("year", $dt);
			}
			else
			{
				$dmon = $maxdate['mon'] - $mindate['mon'];
				if ($dmon < 0)
					$dmon += 12;
				if (($dmon > 1) || (($dmon == 1) && (($maxdate['mday'] > $mindate['mday']) || (($maxdate['mday'] == $mindate['mday']) && ($this->day_ts($maxdate) >= $this->day_ts($mindate))))))
				{
					$dt = $dmon;
					if ($maxdate['mday'] < $mindate['mday'])
						$dt--;
					$dt = (($dt > 1) ? $dt : "") . $this->word_case("month", $dt);
				}
				elseif ($weeks)
				{
					$dt = floor($dt / (3600 * 24 * 7));
					$dt = (($dt > 1) ? $dt : "") . $this->word_case("week", $dt);
				}
				else
				{
					$dt = floor($dt / (3600 * 24));
					$dt = (($dt > 1) ? $dt : "") . $this->word_case("day", $dt);
				}
			}
		}
		$result = str_replace("{time}", $dt, $result);
		return ($this->core_enc == $this->encoding) ? $result : iconv($this->core_enc, $this->encoding, $result);
	}

	/**
	 * Упрощенный вывод даты в удобочитаемом виде, чтобы можно было передавать на вывод только что созданный класс
	 * (напр., echo new DateOutput(...))
	 *
	 * @return string
	 * @since v. 1.0
	 */
	function __toString()
	{
		return $this->generate();
	}

	public static function write($ts, $encoding = "UTF-8", $timezone = "", $time = true, $seconds = false)
	{
		return new DateOutput($ts, $encoding, $timezone, $time, $seconds);
	}

	public static function writeAgo($ts, $encoding = "UTF-8", $timezone = "", $weeks = true, $currtime = 0)
	{
		$dt = new DateOutput($ts, $encoding, $timezone);
		return $dt->ago($weeks, $currtime);
	}
}

?>