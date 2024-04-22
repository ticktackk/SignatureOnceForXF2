CHANGELOG
==========================

## 2.0.7 (`2000770`)

- **Fix:** Since 2.0.6, conversation message signatures are always shown (#53)
- **Change:** Use `templater_macro_post_render` to reset signature visibility status (#55)

## 2.0.6 (`2000670`)

- **Change:** Use templater_macro_pre_render over template modification to force hide user signature (#50)
- **Fix:** Template error: [E_WARNING] foreach() argument must be of type array|object, null given (#51)

## 2.0.5 (`2000570`)

- **Fix:** Incompatibility with XenForo 2.3 (#46)
- **Fix:** ErrorException: `[E_DEPRECATED]` Implicit conversion from float `1799.9865641593933` to int loses precision `src/addons/TickTackk/SignatureOnce/XF/Service/User/ContentChange.php:37` (#48)
- **Fix:** Execution order of code event listener does not match that of resource ID (#49)

## 2.0.4 (`2000470`)

- **Fix:** Incompatibility with Live Content add-on (#44)

## 2.0.3 (`2000370`)

- **Fix:** Showing posts via "Show" link results in template errors being logged (#42)
- **Fix:** Marking post as solution results template error being logged (#41)

## 2.0.2 (`2000270`)

- **Fix:** Calculating current page fails when showing conversation once per page (#38)
- **Fix:** Deleting user does not register queries properly (#39)

## 2.0.1 (`2000170`)

- **Fix:** Rebuilding conversation or thread first content record causes MySQL exception to be logged due to invalid query (#34)
- **Fix:** Rebuilding conversation first content record fails miserably due to reference to wrong table (#35)

## 2.0.0 (`2000070`)

- **New:** Rewrite the add-on to not make use of sub-queries and implement handler system (#30)
- **Change:** Rename options to have tck prefix (#29)
- **Change:** Increase PHP minimum requirement to PHP 7.4 (#30)
- **Change:** Increase XenForo minimum version requirement to 2.2 (#30)

## 1.2.3 (`1020370`)

- **Fix:** Lower requirement for PHP to version 7.1 (#26)

## 1.2.2 (`1020270`)

- **Fix:** Thread action reply type is not checked before calling `getParam()` (#24)

## 1.2.1 (`1020170`)

- **Change:** Execution order of class extensions and template modifications does not match of resource id at XenForo.com (#22)
- **Fix:** Incompatible with XenForo 2.2.x (#17)
- **Fix:** "Show user's signature once per conversation" option is not respected (#18)
- **Fix:** Current page not being calculated correctly when adding last message of conversation (#19)
- **Fix:** Signature once status is not respected after adding message using quick reply (#20)
- **Fix:** Inline editing message does not set show signature status correctly (#21)
- **Misc:** General code clean up

## 1.2.0 (`1020070`)

- **Change:** Drop support for XenForo 2.1.6 or lower (#5)
- **Change:** Increase minimum PHP version requirement to 7.3 (#6)
- **Improvement:** Refactor to improve code readability (#14)
  - **Update:** Container per page content results will now be cached for an hour

## 1.1.5 (`1010570`)

- **Fix:** Reference to non-existent variable in template modification (#11)

## 1.1.4 (`1010470`)

- **New:** Added CHANGELOG.md (#9)
- **Change:** Link to updated developer URL in `addon.json` (#2)
- **Change:** Update support URL to GitHub issues in `addon.json` (#3)
- **Change:** Remove dead Discord server link from `addon.json` (#4)
- **Change:** Move template modification from simple search to regex (#7)

## 1.1.3 (`1010370`)

- **Fix:** Check if container and messages exist before altering controller reply

## 1.1.2 (`1010270`)

- **Fix:** Fatal Error: Call to a member function save() on null

## 1.1.1 (`1010170`)

- **Change:** Improved the query to be more lightweight (once again thanks to @Xon)
- **New:** `ContentInterface` interface for repository now a new method `getMessageCountsForSignatureOnce()`
- **New:** Results will be now cached for 120 seconds (2 minutes)

## 1.1.0 (`1010070`)

- **Change:** Abstracted out most of the parts so support other content types is easier
- **Change:** No longer using sub queries

Thanks to @Xon for giving advice on how `JOIN` works in certain scenarios

## 1.0.3 (`1000370`)

- **Change:** Move options to Threads, discussions and conversations
- **Fix:** Update the phrase to show correct description
- **Fix:**: n+1 query per every message if show once per thread/conversation is enabled is unchecked

## 1.0.2 (`1000270`)

- **New:** Added support for conversations
- **Change:** Updated option description to show will happen if the option state is unchecked
- **Change:** Organized parts of the add-on
- **Change:** Added LICENSE.md and README.md to add-on archive

## 1.0.1 (`1000170`)

- **Fix:** `Template error: key() expects parameter 1 to be array, null given` when adding a post to a new page

## 1.0.0 (`1000070`)

- **Change:** Avoid using static variables for php-pm
- **Fix:** "Show signature once" wasn't respected

## 1.0.0 Alpha 1 (`1000011`)

Initial release