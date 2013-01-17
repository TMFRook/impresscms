define(['./repeat'], function (repeat) {

    /**
     * Pad string with `char` if its' length is smaller than `minLen`
     */
    function lpad(str, minLen, ch) {
        ch = ch || ' ';
        return (str.length < minLen)? repeat(ch, minLen - str.length) + str : str;
    }

    return lpad;

});
