var soularpanicGridMassaction = Class.create(foomanGridMassaction || varienGridMassaction, {
    apply: function($super) {
	var table = $('sales_order_grid_table');

	var methodSel = '.soularpanic_shippingmethod_method';
	
	var shippingOverrides = [];
	table.getElementsBySelector(methodSel).each(function(s) {
	    var id = s.readAttribute('rel');
	    var optionElt = s.getElementsBySelector('option').find(
		function(elt) { return elt.selected });
	    shippingOverrides.push(id + '|' 
				   + s.value + '|' 
				   + optionElt.readAttribute('data-methodName') + '|'
				   + optionElt.readAttribute('data-methodPrice'));
	});

	var addValue = function(elt, targetArr) {
	    var id = elt.readAttribute('rel');
	    var val = elt.value;
	    targetArr.push(id + '|' + val);
	};
	
	new Insertion.Bottom(this.formAdditional,
			     this.fieldTemplate.evaluate({
				 name: 'shipping_override', 
				 value: shippingOverrides }));

	var simpleFields = [
	    {
		arr: [],
		selector: '.soularpanic_shippingmethod_addons',
		postName: 'shipping_addOns'
	    },
	    {
		arr: [],
		selector: '.soularpanic_shippingmethod_customs_value',
		postName: 'shipping_customs_value'
	    },
	    {
		arr: [],
		selector: '.soularpanic_shippingmethod_customs_quantity',
		postName: 'shipping_customs_qty'
	    },
	    {
		arr: [],
		selector: '.soularpanic_shippingmethod_customs_description',
		postName: 'shipping_customs_desc'
	    }
	];

	var formObj = this;
	simpleFields.forEach(function(simpleField) {
	    table.getElementsBySelector(simpleField.selector).each(function(s) {
		addValue(s, simpleField.arr)
	    });
	    new Insertion.Bottom(formObj.formAdditional,
				 formObj.fieldTemplate.evaluate({
				     name: simpleField.postName,
				     value: simpleField.arr }));
	    });

	// var shippingAddOns = [];
	// table.getElementsBySelector(addOnSel).each(addValue(s, shippingAddOns));
	    
	// var customsValues = [];
	// table.getElementsBySelector(customsValSel).each(addValue(s, customsValues));

	// var customsQtys = [];
	// table.getElementsBySelector(customsQtySel).each(addValue(s, customsQtys));
	
	// var customsDescs = [];
	// table.getElementsBySelector(customsDescSel).each(addValue(s, customsDescs));

	

	

	return $super();
    }
});

var soularpanicGrid = Class.create(foomanGrid || varienGrid, {});
