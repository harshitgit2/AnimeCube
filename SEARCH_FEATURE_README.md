# Search and Filtering Feature

## Overview
The AnimeCube project now includes a comprehensive search engine and filtering system that allows users to easily find anime based on various criteria.

## Features Added

### 🔍 Search Functionality
- **Text Search**: Search anime by title (English, Japanese, original), description, or synopsis
- **Real-time Search**: Auto-submit search as you type (with 800ms debounce)
- **Case-insensitive**: Search works regardless of letter case

### 🎯 Filtering Options
- **Genre Filter**: Filter by specific genres (Action, Romance, Comedy, etc.)
- **Status Filter**: Filter by airing status (Airing, Finished Airing, Not yet aired)
- **Type Filter**: Filter by content type (TV, Movie, OVA, ONA, Special)

### 📊 Sorting Options
- **Highest Rated**: Sort by score (descending)
- **Lowest Rated**: Sort by score (ascending)
- **Most Popular**: Sort by popularity
- **Title A-Z**: Alphabetical by title
- **Title Z-A**: Reverse alphabetical
- **Recently Added**: Sort by newest additions
- **Oldest First**: Sort by oldest additions

### 🎨 User Interface
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Clean Layout**: Search bar with filters in organized sections
- **Results Summary**: Shows number of results and applied filters
- **Clear Filters**: One-click button to reset all filters
- **Visual Feedback**: Loading states and hover effects

## Technical Implementation

### Database Queries
- **Prepared Statements**: Secure parameterized queries to prevent SQL injection
- **Dynamic Filtering**: Build WHERE clauses based on user selections
- **Efficient Sorting**: Multiple ORDER BY options for different preferences

### Frontend Features
- **JavaScript Enhancement**: Auto-submit on filter changes for better UX
- **Debounced Search**: Prevents excessive server requests while typing
- **Form Validation**: Proper input sanitization and validation

## How to Test

1. **Setup Requirements**:
   - XAMPP or similar PHP/MySQL environment
   - Import the database schema from `Database/schema.sql`
   - Ensure PHP 7.4+ and MySQL 5.7+

2. **Access the Application**:
   - Start Apache and MySQL in XAMPP
   - Open `http://localhost/AnimeCube/` in your browser

3. **Test Search Features**:
   - Try searching for "Naruto" or "Attack"
   - Test partial matches like "tit" for "Attack on Titan"
   - Search in descriptions with keywords like "young ninja"

4. **Test Filtering**:
   - Select "Action" genre and see results
   - Filter by "Finished Airing" status
   - Combine multiple filters (genre + status + type)

5. **Test Sorting**:
   - Change sort order and verify results
   - Test all sorting options

## File Changes

### Modified Files:
- `client/Card.php`: Added search/filter UI and backend logic

### Key Code Sections:
- **Parameter Processing**: GET parameter handling for search and filters
- **Query Building**: Dynamic SQL construction with prepared statements
- **UI Components**: HTML form with search bar and dropdown filters
- **JavaScript**: Auto-submit functionality and clear filters

## Future Enhancements

- **Advanced Search**: Boolean operators, exact phrase matching
- **Saved Searches**: Allow users to save favorite search combinations
- **Search Suggestions**: Autocomplete with popular searches
- **Filter Combinations**: More complex filter logic (AND/OR operations)
- **Search Analytics**: Track popular searches and filters

## Performance Notes

- **Database Indexing**: Consider adding indexes on frequently searched columns
- **Query Optimization**: The current implementation uses efficient prepared statements
- **Caching**: Results could be cached for popular searches
- **Pagination**: For large datasets, consider adding pagination

## Browser Compatibility

- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **Mobile Browsers**: iOS Safari, Chrome Mobile
- **JavaScript**: ES6+ features used (may need polyfills for older browsers)