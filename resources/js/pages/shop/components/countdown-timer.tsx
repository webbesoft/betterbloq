import { differenceInSeconds, Duration, intervalToDuration } from 'date-fns';
import { useEffect, useState } from 'react';

type TimeLeft = {
    days?: number;
    hours?: number;
    minutes?: number;
    seconds?: number;
};

// Helper function to pad single digits with a leading zero
const padZero = (num: number | undefined): string => {
    if (num === undefined) return '00';
    return num < 10 ? `0${num}` : `${num}`;
};

export const CountdownTimer = ({ endDate }: { endDate: string | null | undefined }) => {
    const calculateTimeLeft = (): TimeLeft => {
        if (!endDate) {
            return { days: 0, hours: 0, minutes: 0, seconds: 0 };
        }
        const now = new Date();
        const end = new Date(endDate);
        const difference = differenceInSeconds(end, now);

        if (difference > 0) {
            const duration: Duration = intervalToDuration({ start: now, end: end });
            return {
                days: duration.days,
                hours: duration.hours,
                minutes: duration.minutes,
                seconds: duration.seconds,
            };
        }
        return { days: 0, hours: 0, minutes: 0, seconds: 0 };
    };

    const [timeLeft, setTimeLeft] = useState<TimeLeft>(calculateTimeLeft());
    const [hasEnded, setHasEnded] = useState(false);

    useEffect(() => {
        const initialTimeLeft = calculateTimeLeft();
        const totalSeconds =
            (initialTimeLeft.days || 0) * 86400 +
            (initialTimeLeft.hours || 0) * 3600 +
            (initialTimeLeft.minutes || 0) * 60 +
            (initialTimeLeft.seconds || 0);
        if (totalSeconds <= 0) {
            setHasEnded(true);
            return;
        } else {
            setHasEnded(false);
        }

        const timer = setInterval(() => {
            const newTimeLeft = calculateTimeLeft();
            const newTotalSeconds =
                (newTimeLeft.days || 0) * 86400 + (newTimeLeft.hours || 0) * 3600 + (newTimeLeft.minutes || 0) * 60 + (newTimeLeft.seconds || 0);

            if (newTotalSeconds <= 0) {
                setTimeLeft({ days: 0, hours: 0, minutes: 0, seconds: 0 });
                setHasEnded(true);
                clearInterval(timer);
            } else {
                setTimeLeft(newTimeLeft);
            }
        }, 1000);

        return () => clearInterval(timer);
    }, [endDate]);

    if (hasEnded) {
        return <div className="text-center font-bold text-red-500">Pool has ended!</div>;
    }

    const formattedDays = timeLeft.days !== undefined ? String(timeLeft.days) : '0';
    const formattedHours = padZero(timeLeft.hours);
    const formattedMinutes = padZero(timeLeft.minutes);
    const formattedSeconds = padZero(timeLeft.seconds);

    const renderDigit = (digit: string) => (
        <span className="countdown-digit" key={Math.random()}>
            {digit}
        </span>
    );

    const renderColon = () => <span className="countdown-colon">:</span>;

    return (
        // Main container for the countdown display
        <div className="countdown-display flex items-center justify-center space-x-1 font-mono text-4xl sm:space-x-2 sm:text-5xl md:text-6xl">
            {/* Days */}
            <div className="countdown-group flex flex-col items-center">
                <div className="countdown-value flex">{formattedDays.split('').map(renderDigit)}</div>
                <span className="countdown-unit text-sm sm:text-base">Days</span>
            </div>

            {renderColon()}

            <div className="countdown-group flex flex-col items-center">
                <div className="countdown-value flex">{formattedHours.split('').map(renderDigit)}</div>
                <span className="countdown-unit text-sm sm:text-base">Hrs</span>
            </div>

            {renderColon()}

            <div className="countdown-group flex flex-col items-center">
                <div className="countdown-value flex">{formattedMinutes.split('').map(renderDigit)}</div>
                <span className="countdown-unit text-sm sm:text-base">Mins</span>
            </div>

            {renderColon()}

            <div className="countdown-group flex flex-col items-center">
                <div className="countdown-value flex">{formattedSeconds.split('').map(renderDigit)}</div>
                <span className="countdown-unit text-sm sm:text-base">Secs</span>
            </div>
        </div>
    );
};
