var bbScriptVars = {};

/* BBScript 1 */
function getBbScriptVar(bbCodeClass, variable) {
	var uid = bbCodeClass.split('-', 1)[0];
	if (uid in bbScriptVars && variable in bbScriptVars[uid]) {
		return bbScriptVars[uid][variable];
	}
	return "";
}

function setBbScriptVar(bbCodeClass, variable, value) {
	var uid = bbCodeClass.split('-', 1)[0];
	if (!(uid in bbScriptVars)) {
		bbScriptVars[uid] = {};
	}
	bbScriptVars[uid][variable] = value;
}

function registerBbScript(bbCodeClass, event, callback) {
	var element = $('.' + bbCodeClass);
	if (event === 'init') {
		$(function () {
			callback.apply(element);
		});
	} else {
		element.off(event);
		element.on(event, callback);
	}
}

/* BBScript 2 */
var targetRegex = /^\w+$/;

function bb_target(id, _this, target, noSelector) {
	if (target == null) {
        return _this;
    }
    if (!target.match(targetRegex)) {
		throw 'Invalid BBScript target';
	}
	return (noSelector ? '' : '.') + 'p' + id + '-' + target;
}

function bb_getVar(id, variable) {
	if (id in bbScriptVars && variable in bbScriptVars[id]) {
		return bbScriptVars[id][variable];
	}
	return '';
}

function bb_setVar(id, variable, value) {
	if (!(id in bbScriptVars)) {
		bbScriptVars[id] = {};
	}
	bbScriptVars[id][variable] = value;
	return bbScriptVars[id][variable];
}

function bb_addClass(id, _this, newClass, target) {
	$(bb_target(id, _this, target)).addClass(bb_target(id, _this, newClass, true));
}

function bb_removeClass(id, _this, newClass, target) {
    $(bb_target(id, _this, target)).removeClass(bb_target(id, _this, newClass, true));
}

function bb_fadeIn(id, _this, duration, target) {
    $(bb_target(id, _this, target)).fadeIn(duration);
}

function bb_fadeOut(id, _this, duration, target) {
    $(bb_target(id, _this, target)).fadeOut(duration);
}

function bb_fadeToggle(id, _this, duration, target) {
    $(bb_target(id, _this, target)).fadeToggle(duration);
}

function bb_hide(id, _this, target) {
    $(bb_target(id, _this, target)).hide();
}

function bb_show(id, _this, target) {
    $(bb_target(id, _this, target)).show();
}

function bb_getText(id, _this, target) {
    return $(bb_target(id, _this, target)).text();
}

function bb_setText(id, _this, text, target) {
    $(bb_target(id, _this, target)).text(text);
}

function bb_getVal(id, _this, target) {
    return $(bb_target(id, _this, target)).val();
}

function bb_setVal(id, _this, val, target) {
    $(bb_target(id, _this, target)).val(val);
}

function bb_slideDown(id, _this, duration, target) {
    $(bb_target(id, _this, target)).slideDown(duration);
}

function bb_slideUp(id, _this, duration, target) {
    $(bb_target(id, _this, target)).slideUp(duration);
}

function bb_slideToggle(id, _this, duration, target) {
    $(bb_target(id, _this, target)).slideToggle(duration);
}

function bb_addDiv(id, _this, _class, target) {
	$(bb_target(id, _this, target)).append('<div class="' + bb_target(id, _this, _class, true) + '"></div>');
}

function bb_removeDiv(id, _this, _class, target) {
    $(bb_target(id, _this, target)).remove(bb_target(id, _this, _class, true));
}

function bb_count(id, _this, array) {
	return array.length;
}

function bb_contains(id, _this, array, needle) {
    return array.includes(needle);
}

function bb_find(id, _this, array, needle) {
    return array.indexOf(needle);
}

function bb_index(id, _this, array, index, value) {
	if (value == null) {
        return array[index];
	}
	array[index] = value;
	return array[index];
}

function bb_append(id, _this, array, value) {
    return array.push(value);
}

function bb_insert(id, _this, array, index, value) {
    array.splice(index, 0, value);
    return value;
}

function bb_pop(id, _this, array) {
    return array.pop();
}

function bb_remove(id, _this, array, index) {
    var value = array[index];
	array.splice(index, 1);
	return value;
}

function bb_reverse(id, _this, array) {
	return array.reverse();
}

function bb_join(id, _this, array, separator) {
    return array.join(separator == null ? '' : separator);
}

function bb_shuffle(id, _this, array) {
    var j, x, i;
    for (i = array.length - 1; i > 0; i--) {
        j = Math.floor(Math.random() * (i + 1));
        x = array[i];
        array[i] = array[j];
        array[j] = x;
    }
    return array;
}

function bb_slice(id, _this, array, start, end) {
    return array.slice(start, end);
}

function bb_split(id, _this, string, separator) {
    return string.split(separator == null ? '' : separator);
}

function bb_lower(id, _this, string) {
    return string.toLowerCase();
}

function bb_upper(id, _this, string) {
    return string.toUpperCase();
}

function bb_trim(id, _this, string) {
    return string.trim();
}

function bb_replace(id, _this, string, needle, replacement) {
	return string.replace(needle, replacement);
}

function bb_random(id, _this) {
    return Math.random();
}

function bb_randomInt(id, _this, min, max) {
    return Math.floor(Math.random() * (max - min) ) + min;
}

function bb_time(id, _this) {
	return new Date().getTime();
}

function bb_clearTimeout(id, _this, handle) {
	clearTimeout(handle);
}

function bb_clearInterval(id, _this, handle) {
    clearInterval(handle);
}

function bb_print(id, _this, message) {
	console.log('BBSCRIPT (' + id + '): ' + String(message));
}