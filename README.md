# school-voip
school project for networking course

Shool project - implement a teamspeak/discord type VoIP prototype. I did that on the internets.

The client runs on chrome (maybe other webkit's), the server runs on windows with whatever the latest apache and php5 versions are at the time of writing.

Setup
=====

1. Get a WASP server (windows, apache, SQLite, PHP), drop it anywhere.
1. Dump everything in htdocs.
1. Run `sqlite3 db.sqlite3` and `.read db.sql`
1. `mkdir scratch`
1. Start the Apache service

Usage
=====

Connect to the server. Have some friends connect to the server. Speak into your microphone hole (loudly) and your buddies will hear the sound that came out of your mouth in their loud speakers. I assume you have a mic and your buddies have speakers. And that none of the levels are at 0%/Mute.

Tech
====

The client is javascript, with some jquery thrown in because everybody uses jquery. It reads the microphone, puts the sound data on the server, and reads available messages from the server.

The server is a hacked together beast. It receives sound data, posts it to all connected users' inboxes, and waits for them to check their inbox. It then sends the next chronological message. Everybody's happy (except SQLite, which cannot handle multiple writes)

There is some notion of a session and of an active connection. The server doesn't clean up stale files properly, but that's beyond the scope of this project. I also don't recommend logging on with the same username twice.
