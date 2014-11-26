//verification if a variable exists : "typeof va", if "undefined", then it doesn't exist

//ask a string from user : var text = prompt('Type smthing :');
// number = parseInt(text);
// '4' == 4 => true
// '4' === 4 => false

// request a "OK or cancel" : confirm("Wanna do smthing ?");

function requestAge() {

	var age = parseInt(prompt("Age ?"));

	if (1<= age && age <= 17) {
		alert("Between 1 and 17")
	} else if (18 <= age && age <= 49) {
		alert("Between 18 and 49");
	} else if (50 < age) {
		alert("You're old");
	} else {
		alert("No negative nor letters please");
	}

	return age;
}

function requestNumber() {
	//Note : would need a verif that's a real number, but lazy
	return prompt("Number ?");
}

function splitNumber(number) {
	var result = [];

	var unite = number%10;
	var decade = ((number%100) - unite)/10;
	var hundred = (number-decade*10-unite)/100;

	result.unite = unite;
	result.decade = decade;
	result.hundred = hundred;

	return result;
}

function TPtradNumber() {
	var number = requestNumber();

	var numberArray = splitNumber(number);

	var uniteString = simpleStringTranslate(numberArray.unite);
	var decadeString = decadeStringTranslate(numberArray.decade);
	var hundredString = simpleStringTranslate(numberArray.hundred)+" hundred";

	if (numberArray.unite == 0 && numberArray.decade == 0) {
		return hundredString+"s";
	} else if (numberArray.unite == 0) {
		return hundredString+" "+decadeString;
	} else if (numberArray.decade == 0) {
		return hundredString+" "+uniteString;
	} else {
		return hundredString+" "+decadeString+" "+uniteString;
	}

}

function simpleStringTranslate(number) {

	switch (number) {
		case 1:
		return "one";
		case 2:
		return "two";
		case 3:
		return "three";
		case 4:
		return "four";
		case 5:
		return "five";
		case 6:
		return "six";
		case 7:
		return "seven";
		case 8:
		return "eight";
		case 9:
		return "nine";
		default :
		return "bug";

	}

}

function decadeStringTranslate(number) {

	switch(number) {
		case 1:
		return "ten";
		case 2:
		return "twenty";
		case 3:
		return "thirty";
		case 4:
		return "forty";
		case 5:
		return "fifty";
		default:
		return simpleStringTranslate(number)+"ty";
	}

}

TPtradNumber();