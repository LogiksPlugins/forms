Forms Plugin
============

Forms deals with rendering, validating and updating forms used to save and edit data into the system

Forms are JSON files controlled by Logiks Form Schema 1.0. They can be places in modules' own folder or can be placed in misc/forms/ path.


### Form Fields
+ hooks : 
+ source : Contain source elements which defines where to store forms data
+ forcefill : Contains the field elements which needs to be filled automatic on form submit
+ gotolink : Contains the link to redirect after form submit
+ reportlink : Where to go on cancel
+ fields : Contains array of field elements,  defined under : Field Elements
+ infoview : Contains the info tabs inside,  defined under : Infoview Document
+ actions : Action button defination, defined under : Form Actions
+ data : If any prefiling of data is required
+ script : loads JS scripts from APPROOT>js>forms>
+ style : loads style css from APPROOT>css>forms>



### Source Elements
+ type : defines the format in which data to be stored(sql, php)
+ table : contains table in which form data should be filled
+ where : contains condition on table fields
+ file : 


### Form Actions
+ label
+ class
+ icon
+ type
+ extras

### Field Elements
+ label : Contains the display text for the field
+ type : is the type of field, defined under : Field Type
+ placeholder
+ group : contains name of tab in which field element should include
+ class
+ tips : 
+ width : 
+ policy : 
+ vmode : 
+ default : Default data to set when creating new
+ form : if set to false, will not appear in forms
+ infoview : if set to false, will not appear in infoview
+ search : if is dropdown, and not multiple, search allows you to search across data.
+ hidden : 
+ readonly
+ disabled
+ required : contains(true, false), defines field element is required or not respectively.
+ noedit
+ nodb
+ multiple
+ max
+ min
+ pattern
+ size
+ src : used by other field attributes like widget, source, module, etc
+ widget : loads widget into forms defined by src
+ source : loads a source file relative to APPROOT, or absolute path defined by src
+ module : loads a module or module component defined by src (format module.component)
+ ajaxchain : contains ajaxchain elements, defined under : AjaxChain
+ autocomplete : contains ajaxchain elements, defined under : Autocomplete
+ validate : JQuery Validate supported rules for validating data
+ concat : 
	+ position : after/before
	+ field : Concat current field with this field
+ slug
+ currency_type : If type=currency, then possible values are usd, eur, gbp, etc

#### Special field proerties
+ no-option : if type=select/dropdown, contains the displays title of dropdown when nothing is selected
+ options : if type=select/dropdown, this is the contains the list of key:value pair of data
+ groupid : if type=dataSelector, this field is used to populate data from data_lists table using groupid
+ table :  if type=dataSelectorFromTable/dataSelectorFromUniques , contains table name from which data to be displayed in dropdown
+ columns : if type=dataSelectorFromTable/dataSelectorFromUniques , contains column name from which data to be displayed in dropdown
+ where : if type=dataSelectorFromTable/dataSelectorFromUniques , contains condition on fields of defined table
+ orderBy : if type=dataSelectorFromTable/dataSelectorFromUniques , contains orderby column
+ groupBy :  if type=dataSelectorFromTable/dataSelectorFromUniques , contains groupby column
+ limit :  if type=dataSelectorFromTable/dataSelectorFromUniques , contains limit column
	


### Field Type
+ text : is input field of type text
+ textarea/longtext/richtextarea/markup
+ currency : is input field of type currency
+ creditcard/debitcard/moneycard
+ email : is input field of type email
+ tel/phone : is input field of type tel
+ mobile : is input field of type mobile
+ tags : is tags field
+ url : is input field of type URL
+ social/brand : is input field of type social text (eg facebook)
+ number : is input field of type number
+ barcode : is input field of type barcode
+ qrcode : is input field of type qrcode
+ search : is input field of type search
+ password : is input field of type password
+ range : is input field of type range
+ color : is input field of type color
+ suggest : allows a editable dropdown for suggested elements from the same column
+ date/datetime/month/year/time  : is input field of type date, datetime etc
+ select/dropdown
+ dataSelector 
+ dataSelectorFromUniques 
+ dataSelectorFromTable
+ dataMethod 
	+ name : Name of method/function to call
	+ valuefield : value to pass to this method
+ radio/checkbox
+ radiolist/checkboxlist
+ file/files/attachment : Fiel field
+ photo/photos/image/avatar/gallery : Photo field
+ jsonfield : Contains JSON (key, value) pair data which uses text data type to store data and retrive
	+ columns : columns for the json data table, it can be a string of column names, or json object
		+ string : eg: colA,colB,colC
		+ json : eg: [label:"colA",type:"date"]
	+ noheader : Table Header is required or not
+ widget :  Loads widget defined by 'src'
	+ src : widget name
+ source : Loads php source file defined by 'src'
	+ src : source file, full path or relative to APPROOT, ROOT
+ static : Static content

### Ajaxchain  Elements
+ target : Contains the target for the ajax call
+ scmd : Contans which service to call for the same
+ src : Contains which file to load for results

### Autocomplete Elements (if array)
+ target : Contains the target for the autocomplete  call
+ scmd : Contans which service to call for the same
+ src : Contains which file to load for results

### Autocomplete Elements (if false)
+ autocomplete: false :: this stops the field to have autocomplete by html5