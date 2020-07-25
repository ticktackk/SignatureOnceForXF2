CHANGELOG
==========================

## 1.2.1 (`1020170`)

- **Fix:** Incompatible with XenForo 2.2.x (#17)

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