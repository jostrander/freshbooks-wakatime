# What is it?

This project sync's "todays" time from WakaTime to corresponding Projects in FreshBooks given that the ID of a Task that exists in Freshbooks is given as well as both project names are the same.

# How to install

First you'll need composer. Download and install composer from their website: http://getcomposer.org

Then run in terminal on OSX/Linux:

```
git clone https://github.com/jostrander/freshbooks-wakatime.git
composer install
mv .env.example .env
```

Once you've done that update your `.env` to your correct API Keys and Freshbooks Task ID. 

If you want this to run every day, you'll need to set up a cron job similar to:

`0 22 * * * /path/to/run.php`

And that's it!




