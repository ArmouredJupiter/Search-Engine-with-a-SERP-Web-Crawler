<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Engine Results Page</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
        font-family: 'Tahoma', sans-serif;
        }
        button[id="reset-button"] {
        display: inline-block;
        float: left;
        vertical-align: top;
        font-size: 20px;
        line-height: 20px;
        padding:8px;
        margin:0;
        border: none;
        background-color: white;
        margin-top: 15px;
        }
        input[type="text"] {
        display: inline-block;
        float: left;
        vertical-align: top;
        margin-left: 30px;
        margin-top: 15px;
        width: 45%;
        padding: 12px;
        border-radius: 7px;
        border: 1px solid black; 
        }
       button[type="submit"] {
       display: inline-block;
       float: left;
       vertical-align: top;
       padding:12px 25px;
       margin-top: 15px;
       border-radius: 7px;
       border: 1px solid black; 
       }
       form {
       overflow: auto;
       }
       button[type="submit"]:hover {
       background-color: silver;
        }
       .keyword-results-container {
        width: 70%;
        border: 1px solid grey;
        margin: 28px auto 0;
        border-radius: 10px;
        box-shadow: 5px 5px 5px grey;
        }
        .no-results-container{
        text-align: center;
        font-size: 1.5em;
        font-weight: bold;
        color: gray;
        margin-top: 50px;
        }
       .result-count-container {
        position: absolute;
        top: 70px;
        margin-left: 625px;
        font-size: 0.8em;
        font-weight: bold;
        color: gray;
        text-align: center;
        }
        a {
        text-decoration: none;
        }  
        * {
        box-sizing: border-box;
        }
        /*the container must be positioned relative:*/
        .autocomplete {
         position: relative;
         display: inline-block;
         }
         /* Autocomplete List */
         .autocomplete-items {
         position: absolute;
         top: 68px;
         margin-left: 27px;
         width: 40%;
         z-index: 999;
         overflow: hidden;
         }
        /* Results Container */
        .keyword-results-container {
         position: relative;
         z-index: 1;
        }
        .autocomplete-items div {
        padding: 10px;
        cursor: pointer;
        background-color: #fff; 
        border-bottom: 1px solid #d4d4d4; 
        text-align: left;
        }
        /*when hovering an item:*/
        .autocomplete-items div:hover {
         background-color: #e9e9e9; 
         }
        /*when navigating through the items using the arrow keys:*/
        .autocomplete-active {
        background-color: DodgerBlue !important; 
        color: #ffffff; 
        }
</style>
</head>
<body>
<button id="reset-button">Search Engine</button>
<form action="serp.php" method="get" onsubmit="search(); return false;">
    <input type="text" name="search-input" id="search-input">
    <button type="submit" value="Search">Search</button>
</form>

<script>
// Get the reset button element
const resetButton = document.getElementById("reset-button");

// Add a click event listener to the reset button
resetButton.addEventListener("click", handleReset);

// Define the function that will handle the reset button click event
function handleReset() {
  // Redirect the user back to the index.php page
  window.location.href = "index.php";
}
</script>

<?php
// Get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];
// Open the ip.txt file in append mode
$file = fopen("ip.txt", "a");
// Write the IP address to the file
fwrite($file, $user_ip . "\n");
// Close the file
fclose($file);
?>

<?php
    // Initialize the number of results variable
    $resultCount = 0;
    // If the search input is empty, don't execute the rest of the code
    if(empty($_GET["search-input"])){
        // Keep the current search results on the screen
        // No need to exit or echo any message
        return;
    }
    if(strlen($_GET["search-input"]) < 2){
        echo "<div class='no-results-container'>Search query should be at least two characters.</div>";
        exit();
    }

    // Read the contents of the database.txt file into a string
    $data = file_get_contents("database.txt");
    // Use regular expressions to extract the product name, URL and information
    preg_match_all("/<b>(.*?)<\/b>.*?<a href='(.*?)'>(.*?)<\/a>.*?(.*)/", $data, $matches);

    $products = array();
    foreach($matches[1] as $index => $name){
        $products[] = array("name" => $name, "url" => $matches[2][$index], "url_text" => $matches[3][$index], "description" => $matches[4][$index]);
    }

    // Get the search query from the GET request and convert it to lowercase
    $query = $_GET["search-input"];
    $query = strtolower($query);
    // Search through the products array for any entries that contain the search query (case-insensitive)
    $results = array();
    foreach ($products as $product) {
        if (strpos(strtolower($product["name"]), $query) !== false) {
            $results[] = $product;
        }
    }
    // Group the results by keyword
    $groupedResults = array();
    foreach($results as $result) {
        $keyword = $result['name']; // use the product name as the keyword
        if (!array_key_exists($keyword, $groupedResults)) {
            $groupedResults[$keyword] = array();
        }
        array_push($groupedResults[$keyword], $result);
    }
    // Display the search results
    if (count($groupedResults) > 0) {
        foreach($groupedResults as $keyword => $keywordResults) {
            echo "<div class='keyword-results-container'>";
            echo "<ul class='keyword-results'>";
            foreach($keywordResults as $result) {
                echo "<div class='single-result-container'>";
                echo "<b style='white-space: nowrap;'>{$result["name"]}</b><br>";
                echo "<a href='{$result["url"]}'>{$result["url_text"]}</a><br>";
                echo "{$result["description"]}";
                echo "</div>";
                // Increment the resultCount variable for each result displayed
                $resultCount++;
            }
            echo "</ul>";
            echo "</div>";
        }
    } else {
        echo "<div class='no-results-container'>No results found for '{$query}'.</div>";
    }
    // Display the number of results found
    echo "<div class='result-count-container'>About {$resultCount} results</div>";
    ?>

<script>
    // stops the page with the search results from refreshing , when a user submits an empty search query 
  document.querySelector("form").addEventListener("submit", function(event) {
    if (!document.querySelector("input[name='search-input']").value) {
      event.preventDefault();
    }
  });
</script>

<script>
function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
          b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /*execute a function when someone clicks in the document:*/
  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
  });
}

/*An array containing all the country names in the world:*/
var countries = [
  <?php
include 'autosuggest_list.txt';
echo '"' . ' ' . '"';
?>
];

/*initiate the autocomplete function on the "myInput" element, and pass along the countries array as possible autocomplete values:*/
autocomplete(document.getElementById("search-input"), countries);
</script>
</body>
</html>
