//post.js
//author: Evie Vanderveeer
//Purpose: This js script is adapated from fcr.js from the FCR toolbar
//This script takes html data from index.html and posts it to 
// kasearchmain.php/initialUserSettings.php and returns data from kasearchmain.php to index.html

$.ajaxSetup({ cache: false });


$(document).ready(function(){
	
		function updateServices () { $.ajax({ url: 'updateService.php' }); }
		
		function updateCI () { $.ajax({ url: 'updateCI.php' }); }

		function pullService () {
			
			var service = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.whitespace,
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			prefetch: {
				url: 'services.json',
				ttl: 1
			}
			});
		
			// passing in `null` for the `options` arguments will result in the default options being used
			$('#kaSearchValuediv .typeahead').typeahead(null, {
			name: 'service',
			source: service,
			});	
		}  //end pullService function
	
		function pullCI () {
			
			var ci = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.whitespace,
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			prefetch: {
				url: 'ci.json',
				ttl: 1
			}
			});
		
			// passing in `null` for the `options` arguments will result in the default options being used
			$('#kaSearchValuediv .typeahead').typeahead(null, {
			name: 'ci',
			source: ci,
			});	
		}  //end pullCI function
	
	$('[data-toggle="tooltip"]').tooltip(); 
	$("#infraProfileSettings").load("initialUserSettings.php");
	$("#regionDisplay").load("regionOptions.php");

	function searchKA () {
	//Display loading gif	
    $("#progressBar").removeClass("out");
    $("#progressBar").addClass("in");  
	
	//convert data to json string
	json = JSON.stringify($('#reportForm').serializeObject());
	
	// Send the data using ajax post
    var posting = $.ajax({
        url:"kasearchmain.php",
        type:"POST",
        data:json,
        contentType: "application/json; charset=utf-8",
        dataType:"json"
    });
	
	// Put the results in searchResult div on index.html
    posting.done(function( response ) {
		
		strURL = "http://infravfctest.web.ppg.com/InfraVFCTest/Core.aspx?Lite&Form=Knowledge&MODE=REVIEW&Database=InfraVFCTest&KNOWLEDGE_REF=";
		
		if (response == null){
			htmlReturn = '<h4 class="text-danger"> The search criteria returned with no results</h4>';
			$( "#searchResult" ).empty();
			$( "#searchResult" ).html(htmlReturn);
		} else {
        if (response.status === "success") {
			count = response.data.length;
			if(count > 300){
				htmlReturn = '<h4 class="text-danger">Please narrow down your search criteria.</h4>';
				$( "#searchResult" ).html(htmlReturn);
			} else {
					$( "#searchResult" ).empty();
					htmlReturn = '<div class="panel-body" ><table class="table table-scrollable"><thead><tr><th>Knowledge Article</th><th>Service</th><th>ID</th></tr></thead><tbody>';
					i = 0;
					$.each(response.data, function(index, server)
					{
						returnedData = server
						htmlReturn = htmlReturn + '<tr><td><a target="_blank" href="' + strURL + returnedData.KNOWLEDGE_REF + '&ID=8730230888915674888888469894">' + returnedData.TITLE + '</a></td>';
						htmlReturn = htmlReturn + "<td>" + returnedData.SERVICE + "</td>";
						htmlReturn = htmlReturn + '<td><a target="_blank" href="' + strURL + returnedData.KNOWLEDGE_REF +  '&ID=8730230888915674888888469894">' + returnedData.KNOWLEDGE_REF +  '</a></td></tr>'; 
						i++;
					});
			
				htmlReturn = htmlReturn + "</tbody></table></div>";
				$( "#searchResult" ).html(htmlReturn);
			
				
		}
		} else if (response.status === "failure") {

            htmlReturn = response.data
            $( "#searchResult" ).empty().html( htmlReturn );
        } else {

            $( "#searchResult" ).empty().html( "Error: Caught Exception" );
        }
		}
        
        //Remove loading bar
        $("#progressBar").removeClass("in");
        $("#progressBar").addClass("out");  
    
    });
	} //End function searchKA
	
	$("#kaSearchValuediv").keypress(function(event) {
        if (event.which == '13') {
            event.preventDefault();
            var search = document.forms["reportForm"]["kaSearchValue"].value;
			if (search == "")
			{
				htmlReturn = '<h4 class="text-danger">Please enter a search value.</h4>';
				$( "#searchResult" ).html(htmlReturn);
			} else {
				searchKA();
			}
		}
    });
	
	$( "#btnSubmit" ).click(function( event ) {
		event.preventDefault();
        var search = document.forms["reportForm"]["kaSearchValue"].value;
		if (search == "")
		{
			htmlReturn = '<h4 class="text-danger">Please enter a search value.</h4>';
			$( "#searchResult" ).html(htmlReturn);
		} else {
			searchKA();
		}
    });
	
	
	//Action when you click on each radio button for the type of search

  	$( 'input[value="kaService"]' ).click(function( event ) {
		htmlSearch = '<input  name="kaSearchValue" type="text" class="form-control typeahead tt-menu" autocomplete="off" spellcheck="false" />';
       $( "#kaSearchValuediv" ).empty();
	   $( "#kaSearchValuediv" ).html(htmlSearch);
	   updateServices();
	   pullService();
    });
	
	$( 'input[value="kaTitleAbstract"]' ).click(function( event ) {
		htmlSearch = '<input  name="kaSearchValue" type="text" class="form-control" autocomplete="off" spellcheck="true" />';
       $( "#kaSearchValuediv" ).empty();
	   $( "#kaSearchValuediv" ).html(htmlSearch);
    });
	
	$( 'input[value="kaConfig"]' ).click(function( event ) {
		htmlSearch = '<input  name="kaSearchValue" type="text" class="form-control typeahead tt-menu" autocomplete="off" spellcheck="false" />';
       $( "#kaSearchValuediv" ).empty();
	   $( "#kaSearchValuediv" ).html(htmlSearch);
	   updateCI();
	   pullCI();
    });
	
	$( 'input[value="kaFull"]' ).click(function( event ) {
		htmlSearch = '<input  name="kaSearchValue" type="text" class="form-control" autocomplete="off" spellcheck="true" />';
       $( "#kaSearchValuediv" ).empty();
	   $( "#kaSearchValuediv" ).html(htmlSearch);
    });
  
  




  // Attach a submit handler to the form
$( "#saveProfile" ).click(function( event ) {

    // Stop form from submitting normally
    event.preventDefault();
    
    json = JSON.stringify($("#profileForm").serializeObject());
    console.log(json);
	
    // Send the data using post
    var posting = $.ajax({
        url:"setUserSettings.php",
        type:"POST",
        data:json,
        contentType: "application/json; charset=utf-8",
        dataType:"json"
    });

    // Put the results in searchResult div on index.html
    posting.done(function( response ) {
		
		htmlReturn = '<h5><b>Profiles Selected:</b>' + response.data + '</h5>';
		$( "#profileResult" ).empty().append(htmlReturn);
		$( '#regionDisplay').empty(  );
		$.ajax({
			url: 'regionOptions.php',
			success: function(data) {
			console.log(data);
          $( '#regionDisplay').append( data );
        }
    });
		});
	
});



	
});
  
  
