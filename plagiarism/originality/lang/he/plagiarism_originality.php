<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   plagiarism_originality
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 /* תיקוני לשון 01 דצמבר 2012*/
 /* תיקוני לשון והגדרת משתנים חדשים 09 מאי 2016*/

$string['originality'] = 'רכיב דרוג מקוריות מסמכים בעברית - גילוי העתקות';
$string['useoriginality'] ='הפעלת הרכיב';
$string['pluginname'] = 'דרוג מקוריות - גילוי העתקות';
$string['studentdisclosure'] = ' הודעה שתוצג לסטודנט כברירת מחדל';
$string['studentdisclosure_help'] = 'הודעה זו תוצג לסטודנטים בעמוד בו יעלו קובץ';

$string['originalityexplain'] = 'הגדרת רכיב דרוג מקוריות';
$string['savedconfigsuccess'] = 'הגדרות הרכיב נשמרו בהצלחה';

$string['originality_key'] = 'מפתח שימוש';
$string['originalitykey'] = 'מפתח שימוש';
$string['originalitykey_help'] = 'על מנת להשתמש ברכיב עליך להיות בעל מפתח שימוש';

$string['originality_view_report'] = 'Allow students to view the report';
$string['originalityviewreport_help'] = 'Allow students to view the report';

$string['originality_api'] = 'כתובת האינטרנט אליה תשלחנה עבודות לבדיקה';

$string['savedconfigsuccess'] = 'הגדרות הרכיב נשמרו בהצלחה';
$string['savedconfigfailed'] = 'מפתח השימוש שהוקש שגוי, הרכיב אינו פעיל';

$string['originality_help'] = ' התקן לגילוי העתקות בעבודות מלל (טקסט) בעברית. אין להשתמש עבור עבודות  בשפה זרה או בעבודות הנדסיות שונות כי המנגנון לא בנוי לכך. לידיעתך: גודל הקובץ להעלעה ישונה ל-100KB.';

$string['studentdisclosuredefault']  = "אני מודע ומסכים שמטלה זו תיבדק לגילוי גניבה ספרותית על ידי קבוצת דירוג מקוריות ואני מסכים <a rel='external' href='https://www.originality.co.il/termsOfUse.html' target='_blank' style='text-decoration:underline'>לתנאי  השימוש</a>.";

$string['originalitystudentdisclosure']  ='עליך לסמן √ במקום המתאים עבור שליחת המטלה לבדיקת מקוריות.<br>ללא סימון זה לא יהיה ניתן להגיש עבודה זו.<br><br>הגשה זו היא מקורית, שייכת לי, נערכה בידיי ובהגשתי זו אני לוקח אחריות על מקוריות הכתוב בתוכה.<br>למעט המקומות שבהם ציינתי שהעבודה נעשתה ע"י אחרים וקישור מתאים נמצא בביבליוגרפיה או במקום הדרוש לכך.<br>';

$string['agree_checked'] = "אני מודע ומסכים שמטלה זו תיבדק לגילוי גניבה ספרותית על ידי קבוצת דירוג מקוריות ואני מסכים <a rel='external' href='https://www.originality.co.il/termsOfUse.html' target='_blank' style='text-decoration:underline'>לתנאי  השימוש</a>.";

$string['originality_fileextmsg']="רק קבצים מהסוגים האלה מותרים: "; //TXT, RTF, DOC, DOCX, PDF";

$string['originality_inprocessmsg'] = "בבדיקה";

$string['originality_info'] = "מידע על מקוריות";

$string['originality_settings'] = "מאפיינים של מרוריות";

$string['settings_key_error'] = "המפתח הסודי שהוקלד שגוי. יש להקליד קוד תקין על מנת לקבל שירות בדיקת מקוריות.";

$string['originality_one_type_submission'] = "לבדיקת מקוריות ניתן להגיש קובץ בודד או תוכן מקוון, אך לא את שניהם. נא לבחור את אחד מהם בלבד.";

//It goes with file_identifier for next version (probably 3.1.8)
$string['originality_allow_multiple_file_submission'] = "אפשרות הגשה של מספר קבצים";

$string['originality_unprocessable'] = 'לא ניתן לעיבוד';

?>