 //Javascript to control the slideshow on the main page
 //https://www.w3schools.com/howto/howto_js_slideshow.asp
 var slideIndex = 1;
   if (window.location.href.includes("about")) {
       getLocationImages();
   } else {
       showSlides(slideIndex);
   }
    // Next/previous controls
    function plusSlides(n) {
      showSlides(slideIndex += n);
    }

    // Thumbnail image controls
    function currentSlide(n) {
      showSlides(slideIndex = n);
    }

    //Shows specific slide
    function showSlides(n) {
      var i;
      var slides = document.getElementsByClassName("mySlides");
      var dots = document.getElementsByClassName("dot");
      if (n > slides.length) {
        slideIndex = 1
      }
      if (n < 1) {
        slideIndex = slides.length
      }
      for (i = 0; i < slides.length; i++) {
          slides[i].style.display = "none";
      }
      for (i = 0; i < dots.length; i++) {
          dots[i].className = dots[i].className.replace(" active", "");
      }
      slides[slideIndex-1].style.display = "block";
      dots[slideIndex-1].className += " active";
    }

    function getLocationImages() {
      var queryString1 = encodeURIComponent("New York Harbor");
      var queryString2 = encodeURIComponent("Greenock");
      var queryString3 = encodeURIComponent("Macclesfield");
      var queryString4 = encodeURIComponent("Le Havre");
      var queryString5 = encodeURIComponent("Theux");
        $.ajax({
          url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString1 + "&prop=pageimages&format=json&pithumbsize=100",
          dataType: 'jsonp',
          success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picUrl = response['query']['pages'][pageId]['thumbnail']['source'];
            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $('#image1').attr("src", picUrl);
          },
          error: function(err) {
            alert("ERROR");
          }
        });
        $.ajax({
          url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString2 + "&prop=pageimages&format=json&pithumbsize=100",
          dataType: 'jsonp',
          success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picUrl = response['query']['pages'][pageId]['thumbnail']['source'];
            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $('#image2').attr("src", picUrl);
          },
          error: function(err) {
            alert("ERROR");
          }
        });
        $.ajax({
          url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString3 + "&prop=pageimages&format=json&pithumbsize=100",
          dataType: 'jsonp',
          success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picUrl = response['query']['pages'][pageId]['thumbnail']['source'];
            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $('#image3').attr("src", picUrl);
          },
          error: function(err) {
            alert("ERROR");
          }
        });
        $.ajax({
          url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString4 + "&prop=pageimages&format=json&pithumbsize=100",
          dataType: 'jsonp',
          success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picUrl = response['query']['pages'][pageId]['thumbnail']['source'];
            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $('#image4').attr("src", picUrl);
          },
          error: function(err) {
            alert("ERROR");
          }
        });
        $.ajax({
          url: "https://en.wikipedia.org/w/api.php?action=query&titles=" + queryString5 + "&prop=pageimages&format=json&pithumbsize=100",
          dataType: 'jsonp',
          success: function(response) {
            var pageId = Object.keys(response['query']['pages']);
            var picUrl = response['query']['pages'][pageId]['thumbnail']['source'];
            //var pic = $('<img id="titleThumb" src="' + picUrl + '" alt="">');
            $('#image5').attr("src", picUrl);
          },
          error: function(err) {
            alert("ERROR");
          }
        });
    }
