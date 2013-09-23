<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package log4php
 */

/**
 * This layout outputs events in a HTML table.
 *
 * Configurable parameters for this layout are:
 * 
 * - title
 * - locationInfo
 *
 * An example for this layout:
 * 
 * {@example ../../examples/php/layout_html.php 19}<br>
 * 
 * The corresponding XML file:
 * 
 * {@example ../../examples/resources/layout_html.properties 18}
 * 
 * The above will print a HTML table that looks, converted back to plain text, like the following:<br>
 * <pre>
 *    Log session start time Wed Sep 9 00:11:30 2009
 *
 *    Time Thread Level Category   Message
 *    0    8318   INFO  root       Hello World!
 * </pre>
 * 
 * @version $Revision: 1379731 $
 * @package log4php
 * @subpackage layouts
 */
class LoggerLayoutHtml2 extends LoggerLayout {
	/**
	 * The <b>LocationInfo</b> option takes a boolean value. By
	 * default, it is set to false which means there will be no location
	 * information output by this layout. If the the option is set to
	 * true, then the file name and line number of the statement
	 * at the origin of the log statement will be output.
	 *
	 * <p>If you are embedding this layout within a {@link LoggerAppenderMail}
	 * or a {@link LoggerAppenderMailEvent} then make sure to set the
	 * <b>LocationInfo</b> option of that appender as well.
	 * @var boolean
	 */
	protected $locationInfo = false;
	
	/**
	 * The <b>Title</b> option takes a String value. This option sets the
	 * document title of the generated HTML document.
	 * Defaults to 'Log4php Log Messages'.
	 * @var string
	 */
	protected $title = "Log4php Log Messages";
	
	/**
	 * The <b>LocationInfo</b> option takes a boolean value. By
	 * default, it is set to false which means there will be no location
	 * information output by this layout. If the the option is set to
	 * true, then the file name and line number of the statement
	 * at the origin of the log statement will be output.
	 *
	 * <p>If you are embedding this layout within a {@link LoggerAppenderMail}
	 * or a {@link LoggerAppenderMailEvent} then make sure to set the
	 * <b>LocationInfo</b> option of that appender as well.
	 */
	public function setLocationInfo($flag) {
		$this->setBoolean('locationInfo', $flag);
	}

	/**
	 * Returns the current value of the <b>LocationInfo</b> option.
	 */
	public function getLocationInfo() {
		return $this->locationInfo;
	}
	
	/**
	 * The <b>Title</b> option takes a String value. This option sets the
	 * document title of the generated HTML document.
	 * Defaults to 'Log4php Log Messages'.
	 */
	public function setTitle($title) {
		$this->setString('title', $title);
	}

	/**
	 * @return string Returns the current value of the <b>Title</b> option.
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @return string Returns the content type output by this layout, i.e "text/html".
	 */
	public function getContentType() {
		return "text/html";
	}
	
	/**
	 * @param LoggerLoggingEvent $event
	 * @return string
	 */
	public function format(LoggerLoggingEvent $event) {
		$sbuf = PHP_EOL;
	
		if ($this->locationInfo) {
			$locInfo = $event->getLocationInformation();
			$sbuf .= htmlentities($locInfo->getFileName(), ENT_QUOTES). ':' . $locInfo->getLineNumber();
			$sbuf .= "<br/>" . PHP_EOL;
		}

		$level = $event->getLevel();
		if ($level->equals(LoggerLevel::getLevelDebug())) {
			$sbuf .= "<font color=\"#339933\"><strong>$level</strong></font>";
		} else if ($level->equals(LoggerLevel::getLevelWarn())) {
			$sbuf .= "<font color=\"#993300\"><strong>$level</strong></font>";
		} else if ($level->equals(LoggerLevel::getLevelError())) {
			$sbuf .= "<font color=\"#BB3333\"><strong>$level</strong></font>";
		} else if ($level->equals(LoggerLevel::getLevelFatal())) {
			$sbuf .= "<font color=\"#FF0000\"><strong>$level</strong></font>";
		} else {
			$sbuf .= "<font color=\"#00CCCC\"><strong>$level</strong></font>";
		}

		$sbuf .= "(" . htmlentities($event->getLoggerName(), ENT_QUOTES);// . ", ";
		$sbuf .= /*$event->getThreadName() . */"): " . date('c') . ' ' . htmlentities($event->getRenderedMessage(), ENT_QUOTES);

		if ($event->getNDC() != null) {
			$sbuf .= PHP_EOL . "<br/>" . "NDC: " . htmlentities($event->getNDC(), ENT_QUOTES);
			$sbuf .= PHP_EOL;
		}


		$sbuf .= "<br/>" . PHP_EOL;
		
		return $sbuf;
	}

	/**
	 * @return string Returns appropriate HTML headers.
	 */
	public function getHeader() {

		$sbuf = PHP_EOL;

		$sbuf = "<div style=\"text-align: left\">" . PHP_EOL;

		return $sbuf;
	}

	/**
	 * @return string Returns the appropriate HTML footers.
	 */
	public function getFooter() {
		$sbuf .= "<br/></div>" . PHP_EOL;

		return $sbuf;
	}
}
