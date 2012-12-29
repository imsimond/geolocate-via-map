# Geolocate via map

A WordPress plugin allowing you to pinpoint a location to be associated with a post - or, via a filter, any other post type.

1.	Start by entering a text-based address: this might be a town name, a postal code, or whatever you have. In the first instance, this should be optimised for searching, so don't worry if it isn't how you'd like it to come out on the page.
2.	Click the 'Find on map' button. This will run a Google search query, which will position the centre of the map at (what it suggests as) the right location. If you're seeing a completely wrong location, go back to step 1 and try a more refined search term.
3.	You can then drag the map around, to position the crosshair over the precise location. The further in you zoom, the more precise it can be.
4.	You can now go back to the text box, and amend the address for on-screen display. Don't worry; you won't lose the positioning of your map, unless you click 'Find on map' again.
5.	Save/publish the post as normal.

The data will be saved as an array with the meta_key `_location`.

## Google Maps API

This plugin uses v3 of the Google Maps API. An API key is *not* required.

## Template tags

Not yet done.

## Map shortcode

To add a map to your post, you can use the shortcode `[map]`. This can also have attributes as follows:

* width, defaulting to 100%
* height, defaulting to 250px
* zoom, defaulting to 9

