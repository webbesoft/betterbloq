import { Star, StarHalfIcon } from 'lucide-react';

export const RatingStars = ({ rating, totalStars = 5, size = 16 }) => {
    if (rating === null || rating === undefined) return null;

    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5 ? 1 : 0;
    const emptyStars = totalStars - fullStars - halfStar;

    return (
        <div className="flex items-center">
            {[...Array(fullStars)].map((_, i) => (
                <Star key={`full-${i}`} fill="currentColor" size={size} className="text-yellow-400" />
            ))}
            {halfStar === 1 && (
                <StarHalfIcon key="half" fill="currentColor" size={size} className="text-yellow-400" />
            )}
            {[...Array(emptyStars)].map((_, i) => (
                <Star key={`empty-${i}`} size={size} className="text-gray-300" />
            ))}
        </div>
    );
};