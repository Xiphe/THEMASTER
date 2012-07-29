
String.prototype.get_readableNameCode = function() {
	var code = '0';
	var val = this;
	// 1
	if( !val.match(/^[A-Za-z]/) ) {
		code += '1';
	} else {
		code += '0';
	}
	// 2
	if( val.match(/[^A-Za-z0-9- ]/) ) {
		code += '1';
	} else {
		code += '0';
	}
	// 3 
	if( val.match(/-{2,}/) ) {
		code += '1';
	} else {
		code += '0';
	}
	// 4
	if( val.match(/ {2,}/) ) {
		code += '1';
	} else {
		code += '0';
	}
	// 5
	if( !val.match(/[A-Za-z0-9]$/) ) {
		code += '1';
	} else {
		code += '0';
	}
	return code;
}
String.prototype.is_readableName = function() {
	if( parseInt( this.get_readableNameCode(), 10 ) == 0) {
		return true;
	} else {
		return false;
	}
}
String.prototype.make_readableName = function() {
	var val = this;
	while( !val.substring(0, 1).match(/[A-Za-z]/) ) {
		val = val.substring(1, val.length);
	}
	val = val
			.replace(/[^A-Za-z0-9- ]/g, '')
			.replace(/-{2,}/g, '-')
			.replace(/ {2,}/g, ' ');
			
	while( !val.substring(val.length-1, val.length).match(/[A-Za-z0-9]/) ) {
		val = val.substring(0, val.length-1);
	}
	
	return val;
}
