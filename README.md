Signature Once for XenForo 2.2.0+
=================================

Description
-----------

This add-on allows user signature to be shown only once per thread or once per page.

Requirements
------------

- PHP 7.4.0+

Options
-------

#### Threads, discussions and conversations

| Name | Description |
|---|---|
| Show user's signature once per conversation | If unchecked, user's signature will be only be shown once per page. |
| Show user's signature once per thread | If unchecked, user's signature will be only be shown once per page. |

Permissions
-----------

#### Forum permissions

- Bypass signature once

#### Conversation permissions

- Bypass signature once

CLI Commands
------------

| Command | Description |
|---|---|
| `xf-rebuild:tck-signature-once-conversation-first-user-message-records` | Rebuilds conversation first user message records.. |
| `xf-rebuild:tck-signature-once-thread-first-user-post-records` | Rebuilds thread first user post records. |

Funding
-------

This add-on was initially funded by [Kevin](https://xenforo.com/community/members/21/)

License
-------

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/ticktackk/SignatureOnceForXF2/blob/master/LICENSE.md) file for details.