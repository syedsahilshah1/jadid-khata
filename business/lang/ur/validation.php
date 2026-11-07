<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
'accepted' => ' :attribute کو منظور کیا جانا چاہیے۔',
'accepted_if' => 'جب :other کی قیمت :value ہو تو :attribute کو منظور کیا جانا چاہیے۔',
'active_url' => ' :attribute ایک درست یو آر ایل نہیں ہے۔',
'after' => ' :attribute کی تاریخ :date کے بعد کی ہونی چاہیے۔',
'after_or_equal' => ' :attribute کی تاریخ :date کے بعد یا اس کے برابر ہونی چاہیے۔',
'alpha' => ' :attribute میں صرف حروف ہونے چاہئیں۔',
'alpha_dash' => ' :attribute میں صرف حروف، نمبرز، ڈیشز اور انڈر سکور ہونے چاہیے۔',
'alpha_num' => ' :attribute میں صرف حروف اور نمبرز ہونے چاہیے۔',
'array' => ' :attribute ایک ارے ہونا چاہیے۔',
'ascii' => ' :attribute میں صرف سنگل بائٹ کے الفانومیریک کیریکٹرز اور علامات ہونی چاہیے۔',
'before' => ' :attribute کی تاریخ :date سے پہلے کی ہونی چاہیے۔',
'before_or_equal' => ' :attribute کی تاریخ :date سے پہلے یا اس کے برابر ہونی چاہیے۔',
'between' => [
    'array' => ' :attribute میں :min اور :max کے درمیان آئٹمز ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :min اور :max کلو بائٹس کے درمیان ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :min اور :max کے درمیان ہونی چاہیے۔',
    'string' => ' :attribute میں :min اور :max کے درمیان حروف ہونے چاہیے۔',
],
'boolean' => ' :attribute کا فیلڈ سچ یا جھوٹ ہونا چاہیے۔',
'confirmed' => ' :attribute کی تصدیق میچ نہیں کرتی۔',
'current_password' => 'پاسورڈ غلط ہے۔',
'date' => ' :attribute ایک درست تاریخ نہیں ہے۔',
'date_equals' => ' :attribute کی تاریخ :date کے برابر ہونی چاہیے۔',
'date_format' => ' :attribute کا فارمیٹ :format سے میل نہیں کھاتا۔',
'decimal' => ' :attribute میں :decimal اعشاریہ مقامات ہونے چاہیے۔',
'declined' => ' :attribute کو مسترد کیا جانا چاہیے۔',
'declined_if' => 'جب :other کی قیمت :value ہو تو :attribute کو مسترد کیا جانا چاہیے۔',
'different' => ' :attribute اور :other مختلف ہونے چاہیے۔',
'digits' => ' :attribute میں :digits اعداد ہونے چاہیے۔',
'digits_between' => ' :attribute میں :min اور :max کے درمیان اعداد ہونے چاہیے۔',
'dimensions' => ' :attribute کی تصویر کے ڈائمینشنز غلط ہیں۔',
'distinct' => ' :attribute کے فیلڈ میں ڈپلیکیٹ ویلیو ہے۔',
'doesnt_end_with' => ' :attribute ان میں سے کسی سے ختم نہیں ہو سکتا: :values۔',
'doesnt_start_with' => ' :attribute ان میں سے کسی سے شروع نہیں ہو سکتا: :values۔',
'email' => ' :attribute ایک درست ای میل پتہ ہونا چاہیے۔',
'ends_with' => ' :attribute ان میں سے کسی ایک سے ختم ہونا چاہیے: :values۔',
'enum' => 'منتخب شدہ :attribute غلط ہے۔',
'exists' => 'منتخب شدہ :attribute غلط ہے۔',
'file' => ' :attribute ایک فائل ہونا چاہیے۔',
'filled' => ' :attribute فیلڈ میں ویلیو ہونی چاہیے۔',
'gt' => [
    'array' => ' :attribute میں :value سے زیادہ آئٹمز ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :value کلو بائٹس سے زیادہ ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :value سے زیادہ ہونی چاہیے۔',
    'string' => ' :attribute میں :value سے زیادہ حروف ہونے چاہیے۔',
],
'gte' => [
    'array' => ' :attribute میں :value یا اس سے زیادہ آئٹمز ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :value کلو بائٹس یا اس سے زیادہ ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :value یا اس سے زیادہ ہونی چاہیے۔',
    'string' => ' :attribute میں :value یا اس سے زیادہ حروف ہونے چاہیے۔',
],
'image' => ' :attribute ایک تصویر ہونی چاہیے۔',
'in' => 'منتخب شدہ :attribute غلط ہے۔',
'in_array' => ' :attribute فیلڈ :other میں موجود نہیں ہے۔',
'integer' => ' :attribute ایک عددی قیمت ہونی چاہیے۔',
'ip' => ' :attribute ایک درست آئی پی ایڈریس ہونا چاہیے۔',
'ipv4' => ' :attribute ایک درست IPv4 ایڈریس ہونا چاہیے۔',
'ipv6' => ' :attribute ایک درست IPv6 ایڈریس ہونا چاہیے۔',
'json' => ' :attribute ایک درست JSON سٹرنگ ہونی چاہیے۔',
'lowercase' => ' :attribute کو lowercase میں ہونا چاہیے۔',
'lt' => [
    'array' => ' :attribute میں :value سے کم آئٹمز ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :value کلو بائٹس سے کم ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :value سے کم ہونی چاہیے۔',
    'string' => ' :attribute میں :value سے کم حروف ہونے چاہیے۔',
],
'lte' => [
    'array' => ' :attribute میں :value سے زیادہ آئٹمز نہیں ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :value کلو بائٹس یا اس سے کم ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :value سے کم یا برابر ہونی چاہیے۔',
    'string' => ' :attribute میں :value سے کم یا برابر حروف ہونے چاہیے۔',
],
'mac_address' => ' :attribute ایک درست MAC ایڈریس ہونا چاہیے۔',
'max' => [
    'array' => ' :attribute میں :max سے زیادہ آئٹمز نہیں ہونے چاہیے۔',
    'file' => ' :attribute کا سائز :max کلو بائٹس سے زیادہ نہیں ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت :max سے زیادہ نہیں ہونی چاہیے۔',
    'string' => ' :attribute میں :max سے زیادہ حروف نہیں ہونے چاہیے۔',
],
'max_digits' => ' :attribute میں :max سے زیادہ اعداد نہیں ہونے چاہیے۔',
'mimes' => ' :attribute میں ان میں سے کسی قسم کی فائل ہونی چاہیے: :values۔',
'mimetypes' => ' :attribute میں ان میں سے کسی قسم کی فائل ہونی چاہیے: :values۔',
'min' => [
    'array' => ' :attribute میں کم سے کم :min آئٹمز ہونے چاہیے۔',
    'file' => ' :attribute کا سائز کم سے کم :min کلو بائٹس ہونا چاہیے۔',
    'numeric' => ' :attribute کی قیمت کم سے کم :min ہونی چاہیے۔',
    'string' => ' :attribute میں کم سے کم :min حروف ہونے چاہیے۔',
],
'min_digits' => ' :attribute میں کم سے کم :min اعداد ہونے چاہیے۔',
'multiple_of' => ' :attribute :value کا ضرب ہونا چاہیے۔',
'not_in' => 'منتخب شدہ :attribute غلط ہے۔',
'not_regex' => ' :attribute کا فارمیٹ غلط ہے۔',
'numeric' => ' :attribute ایک عدد ہونا چاہیے۔',
'password' => [
    'letters' => ' :attribute میں کم از کم ایک حرف ہونا چاہیے۔',
    'mixed' => ' :attribute میں کم از کم ایک بڑا حرف اور ایک چھوٹا حرف ہونا چاہیے۔',
    'numbers' => ' :attribute میں کم از کم ایک نمبر ہونا چاہیے۔',
    'symbols' => ' :attribute میں کم از کم ایک علامت ہونا چاہیے۔',
    'uncompromised' => 'دیا گیا :attribute کسی ڈیٹا لیک میں ظاہر ہوا ہے۔ براہ کرم ایک مختلف :attribute منتخب کریں۔',
],

   'present' => ':attribute فیلڈ موجود ہونی چاہیے۔',
'prohibited' => ':attribute فیلڈ پر پابندی ہے۔',
'prohibited_if' => ':attribute فیلڈ اس صورت میں ممنوع ہے جب :other کی قیمت :value ہو۔',
'prohibited_unless' => ':attribute فیلڈ اس صورت میں ممنوع ہے جب تک کہ :other کی قیمت :values میں سے نہ ہو۔',
'prohibits' => ':attribute فیلڈ :other کو موجود ہونے سے منع کرتی ہے۔',
'regex' => ':attribute کا فارمیٹ غلط ہے۔',
'required' => ':attribute فیلڈ ضروری ہے۔',
'required_array_keys' => ':attribute فیلڈ میں درج ذیل کیز کے لئے اندراجات ہونے چاہئیں: :values۔',
'required_if' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :other کی قیمت :value ہو۔',
'required_if_accepted' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :other کو منظور کیا جائے۔',
'required_unless' => ':attribute فیلڈ اس صورت میں ضروری ہے جب تک کہ :other کی قیمت :values میں نہ ہو۔',
'required_with' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :values موجود ہو۔',
'required_with_all' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :values تمام موجود ہوں۔',
'required_without' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :values موجود نہ ہو۔',
'required_without_all' => ':attribute فیلڈ اس صورت میں ضروری ہے جب :values میں سے کوئی بھی موجود نہ ہو۔',
'same' => ':attribute اور :other کا میل کھانا ضروری ہے۔',
'size' => [
    'array' => ':attribute میں :size اشیاء ہونی چاہئیں۔',
    'file' => ':attribute کا سائز :size کلو بائٹس ہونا چاہیے۔',
    'numeric' => ':attribute کی قیمت :size ہونی چاہیے۔',
    'string' => ':attribute میں :size حروف ہونے چاہیے۔',
],
'starts_with' => ':attribute کو درج ذیل میں سے کسی ایک سے شروع ہونا چاہیے: :values۔',
'string' => ':attribute ایک سٹرنگ ہونی چاہیے۔',
'timezone' => ':attribute ایک درست ٹائم زون ہونا چاہیے۔',
'unique' => ':attribute پہلے ہی لیا جا چکا ہے۔',
'uploaded' => ':attribute اپ لوڈ کرنے میں ناکام رہا۔',
'uppercase' => ':attribute کو بڑی حروف میں ہونا چاہیے۔',
'url' => ':attribute ایک درست یو آر ایل ہونا چاہیے۔',
'ulid' => ':attribute ایک درست ULID ہونا چاہیے۔',
'uuid' => ':attribute ایک درست UUID ہونا چاہیے۔',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

'custom' => [
    'attribute-name' => [
        'rule-name' => 'حسب ضرورت پیغام',
    ],
],

/*
|----------------------------------------------------------------------
| حسب ضرورت توثیقی خصوصیات
|----------------------------------------------------------------------
|
| نیچے دی گئی زبان کی لائنز ہماری اٹریبیوٹ پلیس ہولڈرز کو کسی زیادہ 
| قارئین کے دوست نام سے تبدیل کرنے کے لیے استعمال کی جاتی ہیں جیسے
| "ای میل ایڈریس" کی بجائے "email"۔ اس سے ہمارے پیغامات کو مزید
| واضح بنانے میں مدد ملتی ہے۔
|
*/

'attributes' => [],

'custom-messages' => [
    'quantity_not_available' => 'مقدار :qty :unit دستیاب ہے',
    'this_field_is_required' => 'یہ فیلڈ ضروری ہے',
],


];
