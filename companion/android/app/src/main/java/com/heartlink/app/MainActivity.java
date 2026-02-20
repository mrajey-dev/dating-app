package com.heartlink.app;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.media.RingtoneManager;
import android.os.Build;
import android.os.Bundle;

import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // 🔔 Create/update notification channel for system sound
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
    String channelId = "default_v2"; // NEW channel ID
    String channelName = "Default";

    NotificationManager manager = getSystemService(NotificationManager.class);
    NotificationChannel channel = new NotificationChannel(
            channelId,
            channelName,
            NotificationManager.IMPORTANCE_HIGH
    );

    channel.setSound(
        RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION),
        null
    );

    manager.createNotificationChannel(channel);
}

    }
}
