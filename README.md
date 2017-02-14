This is for showing a way to get in touch and social media links on a family domain that is otherwise only used for e-mail and non-public content.

The contact form has a few simple safeguards. There is a url field that gets hidden to try and fool really simple bots and some sessions to keep track of the number and rate of submissions.

There is also a IP based spam filter. Each senders IP get sha1-hashed and then saved in a database, they are purged if they are older then 10 hours and the matches are counted.

For low traffic sites this form should have sufficent safeguards, if a problem with spam occurs I would recommend just switching to verify submittions with Googles reCAPTCHA.
