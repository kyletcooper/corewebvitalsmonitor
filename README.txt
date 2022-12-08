=== PopBot ===
Contributors: wrdstudio
Tags: page speed, page load, load times, core web vitals, largest contentful paint, LCP, cumulative layout shift, CLS, first input delay, FID, time to first byte, TTFB
Requires at least: 5.7
Tested up to: 6.1
Requires PHP: 7.4.0
License: GPLv3 or later
Stable tag: 1.0.2
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Track page load times for real users across your site.

== Description ==
Core Web Vitals Monitor adds a sidebar to posts & pages and dashboard widget that show you how quickly your pages are loading for real visitors. We report page load times from real users as they experience your site so your data isn't limited to your internet speed.

= Core Web Vitals =
This plugin uses the Google Chrome team's [web vitals package](https://github.com/GoogleChrome/web-vitals) to ensure you get the most accurate data from visitor's loading experience.

= Measurable Insights =
With real user data, you can see if the changes you're making are making a measurable impact for users. We collect all the core web vitals that Google uses as ranking factors:
* Cumulative Layout Shift (CLS)
* First Input Delay (FID)
* Largest Contentful Paint (LCP)

Plus the following experimental/less key statistics:
* First Contentful Paint (FCP)
* Input to Next Paint (INP)
* Time to First Byte (TTFB)

= Tiny and Focused =
The Core Web Vitals Monitor is a tiny plugin focused on one thing: accurate page speed data. Our load time tracker script is only 3.2kb large and is loaded asynchronously so it won't increase your page load times.

== Screenshots ==
1. View the user average of your key statistics.
2. See the spread in values for statistics.

== FAQS ==

= Does this work with the Gutenberg Block Editor? =

Yes! The Core Web Vitals Monitor will appear on the right hand side under your post/page's publishing settings.

= Does this work with the Classic Editor? =

Yes! The Core Web Vitals Monitor will appear on the right hand side under your post/page's publishing settings.

= Can I see page load times for a specific URL? =

Yes, when you open a page or post in your WordPress admin area those statistics are only for the URL of that post. You can scroll down to the bottom of the monitor to see how many visits and what URL the data is for.

= Where can I see the page load times for the entire site? =

If you visit your WordPress admin dashboard, you'll see the Core Web Vitals for all URLs collected on your site.

= Will URL query parameters mess this up?

No. When we track the URL the user is on we strip back everything except the scheme (http or https), host name (www.example.com) and the path (/about-me). If the user is on a page with query parameters or a hash then this will be removed. The only thing that may lead to inconsistent results is if your scheme is inconsistent, for example if users are able to visit the site through HTTP and HTTPS.

== Changelog ==

= Attribution =
GoogleChrome/web-vitals package licensed under Apache License Version 2.0.

= 1.0 =
* First Version